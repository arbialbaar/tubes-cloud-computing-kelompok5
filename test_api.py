import urllib.request
import urllib.parse
import json
import sys

GATEWAY_URL = "http://127.0.0.1:8000"

def send_request(url, method="GET", data=None, headers=None):
    if headers is None:
        headers = {}
    headers["Accept"] = "application/json"
    
    req_data = None
    if data:
        req_data = json.dumps(data).encode("utf-8")
        headers["Content-Type"] = "application/json"
        
    req = urllib.request.Request(url, data=req_data, headers=headers, method=method)
    try:
        with urllib.request.urlopen(req) as res:
            return res.status, json.loads(res.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        try:
            return e.code, json.loads(e.read().decode("utf-8"))
        except Exception:
            return e.code, e.reason
    except Exception as e:
        return 0, str(e)

def run_tests():
    print("=== TESTING REGISTER & LOGIN FLOW ===")
    
    # 1. Test registration validation (missing role)
    test_user_email = f"test_user_{int(urllib.request.urlopen(GATEWAY_URL).length if hasattr(urllib.request.urlopen(GATEWAY_URL), 'length') else 12345)}@example.com"
    # Actually let's use a random email
    import time
    timestamp = int(time.time())
    email = f"user_{timestamp}@example.com"
    
    print(f"\n1. Registering user without role...")
    status, res = send_request(
        f"{GATEWAY_URL}/api/auth/register",
        method="POST",
        data={
            "name": "Test User",
            "email": email,
            "password": "password123",
            "password_confirmation": "password123"
        }
    )
    print(f"Status code (expect 400): {status}")
    print(f"Response: {res}")
    
    if status != 400:
        print("FAIL: Validation should have failed for missing role.")
    else:
        print("PASS: Missing role correctly rejected.")

    # 2. Test registration success
    print(f"\n2. Registering user with valid role (Client)...")
    status, res = send_request(
        f"{GATEWAY_URL}/api/auth/register",
        method="POST",
        data={
            "name": "Test User Client",
            "email": email,
            "password": "password123",
            "password_confirmation": "password123",
            "role": "Client"
        }
    )
    print(f"Status code (expect 201): {status}")
    print(f"Response: {res}")
    
    if status != 201:
        print(f"FAIL: Registration failed. Make sure to run 'docker compose up -d --build' first.")
        return
    else:
        print("PASS: Registration successful.")

    # 3. Test login
    print(f"\n3. Logging in with registered user...")
    status, res = send_request(
        f"{GATEWAY_URL}/api/auth/login",
        method="POST",
        data={
            "email": email,
            "password": "password123"
        }
    )
    print(f"Status code (expect 200): {status}")
    print(f"Response: {res}")
    
    if status != 200:
        print("FAIL: Login failed.")
        return
    else:
        print("PASS: Login successful.")
        
    token = res.get("access_token")
    if not token:
        print("FAIL: Token not found in login response.")
        return
    
    # 4. Test accessing protected assets
    print(f"\n4. Fetching assets using JWT token...")
    status, res = send_request(
        f"{GATEWAY_URL}/api/assets",
        method="GET",
        headers={"Authorization": f"Bearer {token}"}
    )
    print(f"Status code (expect 200): {status}")
    print(f"Response: {res}")
    
    if status != 200:
        print("FAIL: Failed to fetch assets.")
    else:
        print("PASS: Successfully fetched assets using JWT.")

if __name__ == "__main__":
    run_tests()
