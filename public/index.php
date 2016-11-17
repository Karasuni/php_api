<?php
use Interop\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../src/database.php';
require __DIR__ . '/../vendor/autoload.php';
$app = new \Slim\App;

$app->get('/getScore/{id}', '\APIV1:getByID');

class APIV1 {
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
}

$app->run();