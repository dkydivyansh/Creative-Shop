import requests
import json

# --- CONFIGURATION ---
# IMPORTANT: Replace these values with your actual data.

# This is the URL to your API's index.php router.
API_BASE_URL = "https://auth.dkydivyansh.com/api/v1/"

# These are the credentials for the client application making the request.
CLIENT_ID = "ad48558c-8053-11f0-81fa-d7ea139cd55a"  # The ID of your test app
CLIENT_SECRET = "1234"         # The secret of your test app


# --- SCRIPT ---

def exchange_token():
    """
    Prompts for a one-time token and attempts to exchange it for a session token.
    """
    print("--- API Token Exchange Test ---")

    # 1. Get the one-time token from the user (this is the token from the redirect URL)
    one_time_token = input("Enter the 12-digit one-time token: ").strip()
    if not one_time_token:
        print("\n❌ Error: Token cannot be empty.")
        return

    # 2. Prepare the API endpoint URL and the POST data
    endpoint_url = f"{API_BASE_URL}?type=exchange_token"
    payload = {
        'client_id': CLIENT_ID,
        'client_secret': CLIENT_SECRET,
        'token': one_time_token
    }

    print(f"\n▶️  Sending POST request to: {endpoint_url}")
    print(f"▶️  With payload: {payload}")

    try:
        # 3. Make the API call
        response = requests.post(endpoint_url, data=payload, timeout=15)

        # 4. Display the results
        print("\n--- RESPONSE ---")
        print(f"Status Code: {response.status_code}")
        print("Body:")

        # Try to parse and pretty-print the JSON response
        try:
            response_data = response.json()
            print(json.dumps(response_data, indent=2))
        except json.JSONDecodeError:
            print("Could not decode JSON. Raw response:")
            print(response.text)

    except requests.exceptions.RequestException as e:
        print(f"\n❌ An error occurred during the request: {e}")

if __name__ == "__main__":
    exchange_token()