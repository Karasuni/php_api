<?php
use Interop\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../src/database.php';
require __DIR__ . '/../vendor/autoload.php';
$app = new \Slim\App;

/* http://www.slimframework.com/docs/cookbook/enable-cors.html */
$app->options('/{routes:.+}', function ($request, $response, $args) { return $response; });

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});


$app->get('/api/v1/getScore/{id}', 'apiV1:getByID');
$app->get('/api/v1/projects', 'apiV1:getProjects');
$app->post('/api/v1/projects/{name}', 'apiV1:addProject');

class apiV1
{
    protected $ci;

    //Constructor
    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
        //to access items in the container... $this->ci->get('');
    }

    public function getByID(Request $request, Response $response, $args) {
        $id = $args['id'];

        try {
            $db = getDB();

            $sth = $db->prepare("SELECT * FROM students WHERE student_id = :id");
            $sth->execute([':id' => $id]);
            $student = $sth->fetch(PDO::FETCH_OBJ);

            if($student) {
                $db = null;
                $response
                    ->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode($student));
                return $response;
            } else {
                throw new PDOException('No records found.');
            }
        } catch(PDOException $e) {
            return $response->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->write('{"error":{"text":'. json_encode($e->getMessage()) .'}}');
        }
    }

    public function getProjects(Request $request, Response $response, $args) {

        try {
            $db = getDB();

            $sth = $db->query("SELECT * FROM projects");
            $res = $sth->fetchAll(PDO::FETCH_OBJ);

            if($res) {
                $db = null;
                $response
                    ->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode($res));
                return $response;
            } else {
                throw new PDOException('No records found.');
            }
        } catch(PDOException $e) {
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->write('{"error":{"text":'. json_encode($e->getMessage()) .'}}');
        }
    }

    public function addProject(Request $request, Response $response, $args) {

        $parsedBody = $request->getParsedBody();

        $projectName    = $args['name'];
        $description    = $parsedBody['description']    ? $parsedBody['description']    : NULL;
        $activity       = $parsedBody['activity']       ? $parsedBody['activity']       : NULL;
        $lastModified   = $parsedBody['lastModified']   ? $parsedBody['lastModified']   : NULL;
        $approvalStatus = $parsedBody['approvalStatus'] ? $parsedBody['approvalStatus'] : NULL;
        $requester      = $parsedBody['requester']      ? $parsedBody['requester']      : NULL;

        try {
            $db = getDB();

            $sth = $db->prepare("
                INSERT INTO projects (projectName, description, activity, lastModified, approvalStatus, requester)
                VALUES (:projectName, :description, :activity, :lastModified, :approvalStatus, :requester)
                ON DUPLICATE KEY UPDATE 
                  description = VALUES(description),
                  activity = VALUES(activity),
                  lastModified = VALUES(lastModified),
                  approvalStatus = VALUES(approvalStatus),
                  requester = VALUES(requester)
            ");

            $sth->execute([
                ':projectName' => $projectName,
                ':description' => $description,
                ':activity' => $activity,
                ':lastModified' => $lastModified,
                ':approvalStatus' => $approvalStatus,
                ':requester' => $requester
            ]);

            $db = null;
            $response
                ->withStatus(200);

            return $response;
        } catch(PDOException $e) {
            return $response->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->write('{"error":{"text":'. json_encode($e->getMessage()) .'}}');
        }
    }
}

$app->run();