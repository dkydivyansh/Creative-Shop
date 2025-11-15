<?php
class AuthController
{
    public function __construct()
    {
        // FIX: Start the session if it's not already started.
        // This is crucial for reading the error message from the callback.
        register_shutdown_function(function() { $pdo = DBConnection::get(); $pdo = null; });
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    /**
     * Displays the login form.
     */
    public function login()
    {
        $this->showForm('login');
    }

    /**
     * Displays the register form.
     */
    public function register()
    {
        $this->showForm('register');
    }

    /**
     * Displays the authentication form without the main site layout.
     * This is a private helper method.
     *
     * @param string $formType Can be 'login' or 'register'.
     */
    private function showForm($formType = 'login')
    {
        $authError = $_SESSION['auth_error'] ?? null;
        // Add the specific CSS for the auth page
        $extra_styles = ['/public/css/auth.css'];

        // Get the Auth Client ID from the config file
        $authClientId = defined('AUTH_CLIENT_ID') ? AUTH_CLIENT_ID : '';

?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo ucfirst($formType); ?> - My Shop</title>
            <link rel="stylesheet" href="/public/css/style.css">
            <?php if (isset($extra_styles) && is_array($extra_styles)): ?>
                <?php foreach ($extra_styles as $style): ?>
                    <link rel="stylesheet" href="<?php echo htmlspecialchars($style); ?>">
                <?php endforeach; ?>
            <?php endif; ?>
            <!-- Scripts for Animation -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/react/18.2.0/umd/react.production.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.2.0/umd/react-dom.production.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/7.23.5/babel.min.js"></script>
            <script type="module">
                import * as ogl from 'https://unpkg.com/ogl';
                window.ogl = ogl;
            </script>
            <script>
                const authError = <?php echo json_encode($authError); ?>;

                console.log('Auth Error available to JS:', authError);
            </script>
            <!-- Google Fonts -->
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Workbench&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
            <!-- Custom styles for this page -->
            <style>
                .auth-button {
                    justify-content: center;
                    /* This will center the flex items */
                }

                .auth-switch-link {
                    margin-top: 1.5rem;
                    font-size: 0.9rem;
                    color: #bbbbbb;
                }

                .auth-switch-link a {
                    color: #ffffff;
                    font-weight: 600;
                    text-decoration: none;
                }

                .auth-switch-link a:hover {
                    text-decoration: underline;
                }

                .auth-error {
                    background-color: rgba(255, 77, 77, 0.1);
                    border: 1px solid rgba(255, 77, 77, 0.5);
                    color: #ff4d4d;
                    padding: 1rem;
                    border-radius: 8px;
                    margin-bottom: 1.5rem;
                    font-weight: 500;
                }
            </style>
        </head>

        <body class="auth-page">

            <div id="root"></div>

            <script type="text/babel">

                const { useEffect, useRef } = React;

                const GradientBlinds = ({
                    className, dpr, paused = false, gradientColors, angle = 0, noise = 0.3, blindCount = 16,
                    blindMinWidth = 60, mouseDampening = 0.15, mirrorGradient = false, spotlightRadius = 0.5,
                    spotlightSoftness = 1, spotlightOpacity = 1, distortAmount = 0, shineDirection = "left",
                    mixBlendMode = "lighten",
                }) => {
                    const containerRef = useRef(null);
                    
                    useEffect(() => {
                        const container = containerRef.current;
                        if (!container || !window.ogl) return;
                        
                        const { Renderer, Program, Mesh, Triangle } = window.ogl;
                        const MAX_COLORS = 8;
                        const hexToRGB = (hex) => {
                            const c = hex.replace("#", "").padEnd(6, "0");
                            const r = parseInt(c.slice(0, 2), 16) / 255;
                            const g = parseInt(c.slice(2, 4), 16) / 255;
                            const b = parseInt(c.slice(4, 6), 16) / 255;
                            return [r, g, b];
                        };
                        const prepStops = (stops) => {
                            const base = (stops && stops.length ? stops : ["#FF9FFC", "#5227FF"]).slice(0, MAX_COLORS);
                            if (base.length === 1) base.push(base[0]);
                            while (base.length < MAX_COLORS) base.push(base[base.length - 1]);
                            const arr = [];
                            for (let i = 0; i < MAX_COLORS; i++) arr.push(hexToRGB(base[i]));
                            const count = Math.max(2, Math.min(MAX_COLORS, stops?.length ?? 2));
                            return { arr, count };
                        };

                        const renderer = new Renderer({ dpr: dpr ?? (window.devicePixelRatio || 1), alpha: true, antialias: true });
                        const gl = renderer.gl;
                        const canvas = gl.canvas;
                        canvas.style.width = "100%";
                        canvas.style.height = "100vh";
                        canvas.style.display = "block";
                        container.appendChild(canvas);

                        const vertex = `attribute vec2 position; attribute vec2 uv; varying vec2 vUv; void main() { vUv = uv; gl_Position = vec4(position, 0.0, 1.0); }`;
                        const fragment = `
                            #ifdef GL_ES
                            precision mediump float;
                            #endif
                            uniform vec3 iResolution; uniform vec2 iMouse; uniform float iTime; uniform float uAngle; uniform float uNoise; uniform float uBlindCount; uniform float uSpotlightRadius; uniform float uSpotlightSoftness; uniform float uSpotlightOpacity; uniform float uMirror; uniform float uDistort; uniform float uShineFlip; uniform vec3 uColor0; uniform vec3 uColor1; uniform vec3 uColor2; uniform vec3 uColor3; uniform vec3 uColor4; uniform vec3 uColor5; uniform vec3 uColor6; uniform vec3 uColor7; uniform int uColorCount; varying vec2 vUv;
                            float rand(vec2 co){ return fract(sin(dot(co, vec2(12.9898,78.233))) * 43758.5453); }
                            vec2 rotate2D(vec2 p, float a){ float c = cos(a); float s = sin(a); return mat2(c, -s, s, c) * p; }
                            vec3 getGradientColor(float t){
                                float tt = clamp(t, 0.0, 1.0); int count = uColorCount; if (count < 2) count = 2; float scaled = tt * float(count - 1); float seg = floor(scaled); float f = fract(scaled);
                                if (seg < 1.0) return mix(uColor0, uColor1, f); if (seg < 2.0 && count > 2) return mix(uColor1, uColor2, f); if (seg < 3.0 && count > 3) return mix(uColor2, uColor3, f); if (seg < 4.0 && count > 4) return mix(uColor3, uColor4, f); if (seg < 5.0 && count > 5) return mix(uColor4, uColor5, f); if (seg < 6.0 && count > 6) return mix(uColor5, uColor6, f); if (seg < 7.0 && count > 7) return mix(uColor6, uColor7, f);
                                if (count > 7) return uColor7; if (count > 6) return uColor6; if (count > 5) return uColor5; if (count > 4) return uColor4; if (count > 3) return uColor3; if (count > 2) return uColor2; return uColor1;
                            }
                            void mainImage( out vec4 fragColor, in vec2 fragCoord ){
                                vec2 uv0 = fragCoord.xy / iResolution.xy; float aspect = iResolution.x / iResolution.y; vec2 p = uv0 * 2.0 - 1.0; p.x *= aspect; vec2 pr = rotate2D(p, uAngle); pr.x /= aspect; vec2 uv = pr * 0.5 + 0.5;
                                vec2 uvMod = uv; if (uDistort > 0.0) { float a = uvMod.y * 6.0; float b = uvMod.x * 6.0; float w = 0.01 * uDistort; uvMod.x += sin(a) * w; uvMod.y += cos(b) * w; }
                                float t = uvMod.x; if (uMirror > 0.5) { t = 1.0 - abs(1.0 - 2.0 * fract(t)); }
                                vec3 base = getGradientColor(t); vec2 offset = vec2(iMouse.x/iResolution.x, iMouse.y/iResolution.y); float d = length(uv0 - offset); float r = max(uSpotlightRadius, 1e-4); float dn = d / r; float spot = (1.0 - 2.0 * pow(dn, uSpotlightSoftness)) * uSpotlightOpacity; vec3 cir = vec3(spot);
                                float stripe = fract(uvMod.x * max(uBlindCount, 1.0)); if (uShineFlip > 0.5) stripe = 1.0 - stripe; vec3 ran = vec3(stripe);
                                vec3 col = cir + base - ran; col += (rand(gl_FragCoord.xy + iTime) - 0.5) * uNoise;
                                fragColor = vec4(col, 1.0);
                            }
                            void main() { vec4 color; mainImage(color, vUv * iResolution.xy); gl_FragColor = color; }
                        `;
                        const { arr: colorArr, count: colorCount } = prepStops(gradientColors);
                        const uniforms = {
                            iResolution: { value: [gl.drawingBufferWidth, gl.drawingBufferHeight, 1] }, iMouse: { value: [0, 0] }, iTime: { value: 0 }, uAngle: { value: (angle * Math.PI) / 180 }, uNoise: { value: noise }, uBlindCount: { value: Math.max(1, blindCount) }, uSpotlightRadius: { value: spotlightRadius }, uSpotlightSoftness: { value: spotlightSoftness }, uSpotlightOpacity: { value: spotlightOpacity }, uMirror: { value: mirrorGradient ? 1 : 0 }, uDistort: { value: distortAmount }, uShineFlip: { value: shineDirection === "right" ? 1 : 0 },
                            uColor0: { value: colorArr[0] }, uColor1: { value: colorArr[1] }, uColor2: { value: colorArr[2] }, uColor3: { value: colorArr[3] }, uColor4: { value: colorArr[4] }, uColor5: { value: colorArr[5] }, uColor6: { value: colorArr[6] }, uColor7: { value: colorArr[7] }, uColorCount: { value: colorCount },
                        };
                        const program = new Program(gl, { vertex, fragment, uniforms });
                        const geometry = new Triangle(gl);
                        const mesh = new Mesh(gl, { geometry, program });
                        
                        let raf;
                        const mouseTarget = [0, 0];
                        let lastTime = 0;
                        let firstResize = true;

                        const resize = () => {
                            const rect = container.getBoundingClientRect();
                            renderer.setSize(rect.width, rect.height);
                            uniforms.iResolution.value = [gl.drawingBufferWidth, gl.drawingBufferHeight, 1];
                            if (blindMinWidth && blindMinWidth > 0) {
                                const maxByMinWidth = Math.max(1, Math.floor(rect.width / blindMinWidth));
                                const effective = blindCount ? Math.min(blindCount, maxByMinWidth) : maxByMinWidth;
                                uniforms.uBlindCount.value = Math.max(1, effective);
                            } else {
                                uniforms.uBlindCount.value = Math.max(1, blindCount);
                            }
                            if (firstResize) {
                                firstResize = false;
                                const cx = gl.drawingBufferWidth / 2;
                                const cy = gl.drawingBufferHeight / 2;
                                uniforms.iMouse.value = [cx, cy];
                                mouseTarget[0] = cx;
                                mouseTarget[1] = cy;
                            }
                        };
                        
                        const ro = new ResizeObserver(resize);
                        ro.observe(container);

                        const onPointerMove = (e) => {
                            const rect = canvas.getBoundingClientRect();
                            const scale = renderer.dpr || 1;
                            const x = (e.clientX - rect.left) * scale;
                            const y = (rect.height - (e.clientY - rect.top)) * scale;
                            mouseTarget[0] = x;
                            mouseTarget[1] = y;
                            if (mouseDampening <= 0) { uniforms.iMouse.value = [x, y]; }
                        };
                        window.addEventListener("pointermove", onPointerMove);

                        const loop = (t) => {
                            raf = requestAnimationFrame(loop);
                            uniforms.iTime.value = t * 0.001;
                            if (mouseDampening > 0) {
                                if (!lastTime) lastTime = t;
                                const dt = (t - lastTime) / 1000;
                                lastTime = t;
                                const tau = Math.max(1e-4, mouseDampening);
                                let factor = 1 - Math.exp(-dt / tau);
                                if (factor > 1) factor = 1;
                                const cur = uniforms.iMouse.value;
                                cur[0] += (mouseTarget[0] - cur[0]) * factor;
                                cur[1] += (mouseTarget[1] - cur[1]) * factor;
                            } else { lastTime = t; }
                            if (!paused) { renderer.render({ scene: mesh }); }
                        };
                        raf = requestAnimationFrame(loop);

                        return () => {
                            cancelAnimationFrame(raf);
                            window.removeEventListener("pointermove", onPointerMove);
                            ro.disconnect();
                            if (canvas.parentElement === container) { container.removeChild(canvas); }
                        };
                    }, [
                        dpr, paused, gradientColors, angle, noise, blindCount, blindMinWidth, mouseDampening,
                        mirrorGradient, spotlightRadius, spotlightSoftness, spotlightOpacity, distortAmount, shineDirection,
                    ]);

                    return <div ref={containerRef} className={className} style={{ mixBlendMode }} />;
                };

                function App() {
                    const formType = '<?php echo $formType; ?>';
                    const clientId = '<?php echo $authClientId; ?>';


                    const handleLogin = () => {
        if (clientId) {
            // Redirect the current page to the login URL
            window.location.href = `https://auth.dkydivyansh.com/auth/login?appauth=${clientId}`;
        } else {
            alert('Auth client ID is not configured.');
        }
    };

    const handleRegister = () => {
        if (clientId) {
            // Redirect the current page to the register URL
            window.location.href = `https://auth.dkydivyansh.com/auth/register?appauth=${clientId}`;
        } else {
            alert('Auth client ID is not configured.');
        }
    };

                    return (
                        <React.Fragment>
                            <div id="background-container">
                                <GradientBlinds
                                    gradientColors={['#FF9FFC', '#5227FF']}
                                    angle={0}
                                    noise={0.3}
                                    blindCount={12}
                                    blindMinWidth={50}
                                    spotlightRadius={0.5}
                                    spotlightSoftness={1}
                                    spotlightOpacity={1}
                                    mouseDampening={0.15}
                                    distortAmount={0}
                                    shineDirection="left"
                                    mixBlendMode="lighten"
                                />
                            </div>
                            <header className="page-header">
                                <a href="/" className="logo">SHOP - dkydivyansh</a>
                            </header>

                            <div className="auth-container">
                                {formType === 'login' ? (
                                    <div className="auth-card">
                                        <img src="https://dkydivyansh.com/wp-content/uploads/2025/08/D-2.png" alt="icon" className="auth-card-icon" onError={(e) => e.target.style.display='none'}/>
                                        <h2>Login</h2>
                                        <?php
                                        // Check for an error message from the callback
                                        if (isset($_SESSION['auth_error'])):
                                        ?>
        <div class="auth-error-box">
            <?php
                                            echo htmlspecialchars($_SESSION['auth_error']);
                                            // Clear the error from the session so it doesn't show again
                                            unset($_SESSION['auth_error']);
            ?>
        </div>
    <?php endif; ?>
                                        <p>Welcome back! Sign in to access your account, view your orders, and manage your profile.</p>
                                        <button onClick={handleLogin} className="auth-button">
                                            <span>Login with dkydivyansh.com</span>
                                            <span className="material-symbols-outlined arrow">arrow_forward</span>
                                        </button>
                                        <p className="auth-switch-link">
                                            Don't have an account? <a href="/register">Register</a>
                                        </p>
                                    </div>
                                ) : (
                                    <div className="auth-card">
                                        <img src="https://dkydivyansh.com/wp-content/uploads/2025/08/D-2.png" alt="icon" className="auth-card-icon" onError={(e) => e.target.style.display='none'}/>
                                        <h2>Register</h2>
                                        <p>New here? Create an account to start shopping, save your favorite items, and enjoy a seamless checkout experience.</p>
                                        <button onClick={handleRegister} className="auth-button">
                                            <span>Register with dkydivyansh.com</span>
                                            <span className="material-symbols-outlined arrow">arrow_forward</span>
                                        </button>
                                        <p className="auth-switch-link">
                                            Already have an account? <a href="/login">Login</a>
                                        </p>
                                    </div>
                                )}
                            </div>
                        </React.Fragment>
                    );
                }

                ReactDOM.render(<App />, document.getElementById('root'));
            </script>
        </body>

        </html>
<?php
    }
}
