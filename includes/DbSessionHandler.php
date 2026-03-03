<?php
/**
 * Database-backed PHP session handler.
 * Stores sessions in the `sessions` table so they are shared across multiple servers.
 * Implements PHP's SessionHandlerInterface for use with session_set_save_handler().
 */
class DbSessionHandler implements SessionHandlerInterface
{
    private $pdo;

    /**
     * Max session lifetime in seconds.
     * Defaults to PHP's session.gc_maxlifetime (usually 1440 = 24 minutes).
     * We override this to 30 days to match the SSO access_token lifetime.
     */
    private $maxLifetime;

    public function __construct(PDO $pdo, int $maxLifetime = 2592000)
    {
        $this->pdo = $pdo;
        $this->maxLifetime = $maxLifetime;
    }

    /**
     * Called when the session is opened. No-op since DB connection is already ready.
     */
    public function open(string $savePath, string $sessionName): bool
    {
        return true;
    }

    /**
     * Called when the session is closed. No-op.
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Reads session data from the database.
     * Returns empty string if session doesn't exist or has expired.
     */
    public function read(string $sessionId): string|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT data FROM sessions WHERE id = :id AND expires_at > NOW()"
        );
        $stmt->execute([':id' => $sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row['data'] : '';
    }

    /**
     * Writes session data to the database.
     * Uses INSERT ... ON DUPLICATE KEY UPDATE for upsert behavior.
     */
    public function write(string $sessionId, string $data): bool
    {
        $expiresAt = date('Y-m-d H:i:s', time() + $this->maxLifetime);

        $stmt = $this->pdo->prepare(
            "INSERT INTO sessions (id, data, expires_at) 
             VALUES (:id, :data, :expires_at)
             ON DUPLICATE KEY UPDATE data = :data2, expires_at = :expires_at2"
        );

        return $stmt->execute([
            ':id' => $sessionId,
            ':data' => $data,
            ':expires_at' => $expiresAt,
            ':data2' => $data,
            ':expires_at2' => $expiresAt,
        ]);
    }

    /**
     * Destroys a session by deleting it from the database.
     */
    public function destroy(string $sessionId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
        return $stmt->execute([':id' => $sessionId]);
    }

    /**
     * Garbage collection — removes expired sessions.
     * Called periodically by PHP based on session.gc_probability / session.gc_divisor.
     */
    public function gc(int $maxLifetime): int|false
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE expires_at < NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
