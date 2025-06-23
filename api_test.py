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
        "password": "secret"
    }
    resp = requests.post(f"{BASE_URL}/users", json=user_payload)
    print_result("Create user", resp)

    if resp.ok:
        user_id = resp.json().get("id")

        # Login to obtain a token
        login_data = {"username": user_payload["username"], "password": user_payload["password"]}
        resp = requests.post(f"{BASE_URL}/login", data=login_data)
        print_result("Login", resp)
        token = resp.json().get("access_token") if resp.ok else None
        headers = {"Authorization": f"Bearer {token}"} if token else {}

        # 2. Get the created user
        resp = requests.get(f"{BASE_URL}/users/{user_id}")
        print_result("Get user", resp)

        # 3. Create an article
        article_payload = {
            "user_id": user_id,
            "title": "Hello",
            "content": "Testing from another container"
        }
        resp = requests.post(f"{BASE_URL}/articles", json=article_payload, headers=headers)
        print_result("Create article", resp)

        if resp.ok:
            article_id = resp.json().get("id")

            # 4. Get single article
            resp = requests.get(f"{BASE_URL}/articles/{article_id}")
            print_result("Get article", resp)

            # 5. Add a comment to the article
            comment_payload = {
                "post_id": article_id,
                "user_id": user_id,
                "content": "Nice post!"
            }
            resp = requests.post(f"{BASE_URL}/articles/{article_id}/comments", json=comment_payload, headers=headers)
            print_result("Create comment", resp)

            # 6. List comments for the article
            resp = requests.get(f"{BASE_URL}/articles/{article_id}/comments")
            print_result("List comments", resp)

            # 7. Create a forum category
            category_payload = {
                "name": "General",
                "description": "General discussion"
            }
            resp = requests.post(f"{BASE_URL}/forum/categories", json=category_payload, headers=headers)
            print_result("Create category", resp)

            if resp.ok:
                category_id = resp.json().get("id")

                # 8. List categories
                resp = requests.get(f"{BASE_URL}/forum/categories")
                print_result("List categories", resp)

                # 9. Create a thread
                thread_payload = {
                    "title": "Welcome",
                    "content": "Introduce yourself here",
                    "user_id": user_id,
                    "category_id": category_id
                }
                resp = requests.post(f"{BASE_URL}/forum/threads", json=thread_payload, headers=headers)
                print_result("Create thread", resp)

                if resp.ok:
                    thread_id = resp.json().get("id")

                    # 10. List threads
                    resp = requests.get(f"{BASE_URL}/forum/threads")
                    print_result("List threads", resp)

                    # 11. Get single thread
                    resp = requests.get(f"{BASE_URL}/forum/threads/{thread_id}")
                    print_result("Get thread", resp)

                    # 12. Add a reply
                    reply_payload = {
                        "thread_id": thread_id,
                        "user_id": user_id,
                        "content": "Hello everyone"
                    }
                    resp = requests.post(f"{BASE_URL}/forum/threads/{thread_id}/replies", json=reply_payload, headers=headers)
                    print_result("Create reply", resp)

                    # 13. List replies
                    resp = requests.get(f"{BASE_URL}/forum/threads/{thread_id}/replies")
                    print_result("List replies", resp)


    # Final: list all articles
    resp = requests.get(f"{BASE_URL}/articles")
    print_result("List articles", resp)


if __name__ == "__main__":
    main()
