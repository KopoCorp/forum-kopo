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
            resp = requests.post(f"{BASE_URL}/articles/{article_id}/comments", json=comment_payload)
            print_result("Create comment", resp)

            # 6. List comments for the article
            resp = requests.get(f"{BASE_URL}/articles/{article_id}/comments")
            print_result("List comments", resp)

            # 7. Create a forum category
            category_payload = {
                "name": "General",
                "description": "General discussion"
            }
            resp = requests.post(f"{BASE_URL}/forum/categories", json=category_payload)
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
                resp = requests.post(f"{BASE_URL}/forum/threads", json=thread_payload)
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
                    resp = requests.post(f"{BASE_URL}/forum/threads/{thread_id}/replies", json=reply_payload)
                    print_result("Create reply", resp)

                    # 13. List replies
                    resp = requests.get(f"{BASE_URL}/forum/threads/{thread_id}/replies")
                    print_result("List replies", resp)

            # 14. Like the article
            like_payload = {
                "user_id": user_id,
                "target_type": "article",
                "target_id": article_id
            }
            resp = requests.post(f"{BASE_URL}/likes", json=like_payload)
            print_result("Create like", resp)

            # 15. Create a tag
            tag_payload = {"name": "news"}
            resp = requests.post(f"{BASE_URL}/tags", json=tag_payload)
            print_result("Create tag", resp)

            if resp.ok:
                tag_id = resp.json().get("id")

                # 16. Assign tag to article
                assign_payload = {"tag_id": tag_id}
                resp = requests.post(f"{BASE_URL}/articles/{article_id}/tags", json=assign_payload)
                print_result("Assign tag", resp)

                # 17. Filter articles by tag
                resp = requests.get(f"{BASE_URL}/articles", params={"tag": tag_id})
                print_result("Filter articles", resp)

    # Final: list all articles
    resp = requests.get(f"{BASE_URL}/articles")
    print_result("List articles", resp)


if __name__ == "__main__":
    main()
