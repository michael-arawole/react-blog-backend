<?php 
// +------------------------------------------------------------------------+
// | @author        : Michael Arawole (Logad Networks)
// | @author_url    : https://www.logad.net
// | @author_email  : logadscripts@gmail.com
// | @date          : 19 Sep, 2022 01:20PM
// +------------------------------------------------------------------------+

// +----------------------------+
// | PHP API Handler
// +----------------------------+

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../backend/inc/autoload.php';


header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
$app = AppFactory::create();

// Add body parsing
$app->addBodyParsingMiddleware();
// Add Error Handling Middleware
$app->addErrorMiddleware(true, false, false);

// Automatically add base path (fix)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($scriptDir == "/") $scriptDir = "";
$app->setBasePath($scriptDir);

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

// Login //
$app->post('/login', function (Request $request, Response $response, $args) {
    $response_data = defaultResponse();
    $result = Auth::login(cleanPostData($request->getParsedBody()));
    $response_data['message'] = $result['message'];
    if ($result['status'] === true) {
        $response_data['error'] = false;
        $response_data['message'] = 'success';
        $response_data['data'] = $result['data'];
        $response->getBody()->write(json_encode($response_data));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
    } else {
        $response->getBody()->write(json_encode($response_data));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(500);
    }
    return $response;
});

// +----------------------------+
// | Blogs
// +----------------------------+

// All Blogs //
$app->get('/blogs', function (Request $request, Response $response, $args) {
    $response_data['error'] = false;
    $response_data['message'] = 'success';
    $response_data['data'] = Articles::getRecent();
    $response->getBody()->write(json_encode($response_data));
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

// New Blog //
$app->post('/blogs', function (Request $request, Response $response, $args) {
    $response_data = defaultResponse();
    $result = Articles::store(cleanPostData($request->getParsedBody()));
    $response_data['message'] = $result['message'];
    if ($result['status'] === true) {
        $response_data['error'] = false;
        $response_data['message'] = 'success';
        $response_data['data'] = $result['data'];
        $response->getBody()->write(json_encode($response_data));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
    } else {
        $response->getBody()->write(json_encode($response_data));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(500);
    }
    return $response;
});

// Single Blog //
$app->get('/blogs/{id}', function (Request $request, Response $response, $args) {
    $response_data = defaultResponse();
    $article = Articles::byID($args['id']);
    if (!empty($article)) {
        $article->content = nl2br(html_entity_decode($article->content));
        $response_data['error'] = false;
        $response_data['message'] = 'success';
        $response_data['data'] = $article;
        $response->getBody()->write(json_encode($response_data));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
    } else {
        $response_data['message'] = 'Could not find article';
        $response->getBody()->write(json_encode($response_data));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(400);
    }
    return $response;
});

// Delete Blog //
$app->delete('/blogs/{id}', function (Request $request, Response $response, $args) {
    $response_data = defaultResponse();
    if (Articles::delete($args['id'])) {
        $response_data['error'] = false;
        $response_data['message'] = 'Article delleted';
        $response->getBody()->write(json_encode($response_data));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
    } else {
        $response_data['message'] = 'Failed to delete article';
        $response->getBody()->write(json_encode($response_data));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(400);
    }
    return $response;
});

$app->run();

function cleanPostData($post_data) {
    $post = new stdClass();
    foreach ($post_data as $key => $value) {
        $post->$key = htmlentities($value);
    }
    return $post;
}

function defaultResponse() {
    return ["error" => true, "message" => "Unknown error occurred"];
}