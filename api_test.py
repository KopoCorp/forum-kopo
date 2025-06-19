import os
import sys
import requests

BASE_URL = os.environ.get("API_URL", sys.argv[1] if len(sys.argv) > 1 else "http://localhost:8000")


def print_result(description, response):
    status = response.status_code
    try:
        data = response.json()
    except Exception:
        data = response.text
    print(f"{description}: {status} -> {data}")


def main():
    # 1. Create user
    user_payload = {
        "username": "tester",
        "email": "tester@example.com",
        "pass_hash": "secret"
    }
    resp = requests.post(f"{BASE_URL}/users", json=user_payload)
    print_result("Create user", resp)

    if resp.ok:
        user_id = resp.json().get("id")
        # 2. Get the created user
        resp = requests.get(f"{BASE_URL}/users/{user_id}")
        print_result("Get user", resp)

        # 3. Create an article
        article_payload = {
            "user_id": user_id,
            "title": "Hello",
            "content": "Testing from another container"
        }
        resp = requests.post(f"{BASE_URL}/articles", json=article_payload)
        print_result("Create article", resp)

    # 4. List articles
    resp = requests.get(f"{BASE_URL}/articles")
    print_result("List articles", resp)


if __name__ == "__main__":
    main()
