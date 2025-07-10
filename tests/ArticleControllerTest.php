<?php
require_once __DIR__ . '/../controllers/ArticleController.php';
class FakeAPI {
    private $responses;
    public function __construct(array $responses) {
        $this->responses = $responses;
    }
    public function request($endpoint, $method = 'GET', $data = [], $auth = false, $json = true) {
        if (!isset($this->responses[$endpoint])) {
            throw new Exception("Unexpected endpoint: $endpoint");
        }
        return $this->responses[$endpoint];
    }
    public function isLoggedIn() { return false; }
}
class ArticleControllerTest extends PHPUnit\Framework\TestCase {
    public function testFetchArticleWithTagsResolvesIds() {
        $responses = [
            '/articles/1' => ['id'=>1,'title'=>'Demo','content'=>'Body','tags'=>[2,3]],
            '/tags' => [
                ['id'=>2,'name'=>'Tech'],
                ['id'=>3,'name'=>'Dev']
            ]
        ];
        $api = new FakeAPI($responses);
        $controller = new ArticleController($api);
        $method = new ReflectionMethod(ArticleController::class, 'fetchArticleWithTags');
        $method->setAccessible(true);
        $article = $method->invoke($controller, 1, false);
        $this->assertEquals([
            ['id'=>2,'name'=>'Tech'],
            ['id'=>3,'name'=>'Dev']
        ], $article['tags']);
    }
}
