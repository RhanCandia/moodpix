<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\PhpRenderer;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

/**
 * GET /
 * Render Frontend
 */
$app->get('/', function (Request $request, Response $response) {

    $renderer = new PhpRenderer(__DIR__ . '/../public/');
    return $renderer->render($response, 'index.html');
});

$app->group('/moods', function (RouteCollectorProxy $group) {

    /**
     * GET /moods
     * Get all mood
     */
    $group->get('', function (Request $request, Response $response) {

        try {

            $db = new DB();
            $db->connect();

            $sql = "SELECT * FROM mood";
            $stmt = $db->conn->query($sql);
            $moods = $stmt->fetchAll(PDO::FETCH_OBJ);

            $db->close();

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'moods' => $moods
            ]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (PDOException $e) {

            $response->getBody()->write(json_encode([
                'message' => $e->getMessage(),
            ]));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    });

    /**
     * POST /moods
     * Create a mood
     */
    $group->post('', function (Request $request, Response $response) {

        $data = $request->getParsedBody();

        if (empty($data['mood_color'])) {

            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Mood color is required'
            ]));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

        try {

            $db = new DB();
            $db->connect();

            $insertSql = "INSERT INTO mood (mood_color, mood_note) VALUE (:mood_color, :mood_note)";
            $stmt = $db->conn->prepare($insertSql);
            $stmt->bindParam(':mood_color', $data['mood_color']);
            $stmt->bindParam(':mood_note', $data['mood_note']);

            $stmt->execute();
            $lastId = $db->conn->lastInsertId();

            $selectSql = "SELECT * FROM mood WHERE id = $lastId";
            $stmt = $db->conn->query($selectSql);
            $mood = $stmt->fetch(PDO::FETCH_OBJ);

            $db->close();

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Mood successfully created',
                'mood' => $mood
            ]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);

        } catch (PDOException $e) {

            $response->getBody()->write(json_encode([
                'message' => $e->getMessage(),
            ]));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    });

    /**
     * PUT /moods/{id}
     * Update a mood
     */
    $group->put('/{id}', function (Request $request, Response $response, $args) {

        $mood_id = (int) $args['id'];
        $data = $request->getParsedBody();

        try {

            $db = new DB();
            $db->connect();

            $sql = "UPDATE mood SET mood_color = :mood_color, mood_note = :mood_note WHERE id = :mood_id";
            $stmt = $db->conn->prepare($sql);
            $stmt->bindParam(':mood_color', $data['mood_color']);
            $stmt->bindParam(':mood_note', $data['mood_note']);
            $stmt->bindParam(':mood_id', $mood_id);

            $stmt->execute();

            $db->close();

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Mood successfully updated'
            ]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);

        } catch (PDOException $e) {

            $response->getBody()->write(json_encode([
                'message' => $e->getMessage(),
            ]));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    });

    /**
     * DELETE /moods/{id}
     * Delete a mood
     */
    $group->delete('/{id}', function (Request $request, Response $response, $args) {

        $mood_id = (int) $args['id'];

        try {

            $db = new DB();
            $db->connect();

            $sql = "DELETE FROM mood WHERE id = :mood_id";
            $stmt = $db->conn->prepare($sql);
            $stmt->bindParam(':mood_id', $mood_id);

            $stmt->execute();

            $db->close();

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Mood successfully deleted'
            ]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);

        } catch (PDOException $e) {

            $response->getBody()->write(json_encode([
                'message' => $e->getMessage(),
            ]));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    });
});

$app->run();