<?php
use Interop\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../src/database.php';
require __DIR__ . '/../vendor/autoload.php';
$app = new \Slim\App;

///* https://github.com/itsgoingd/clockwork#slim-2 */
//$app->add(
//    new Clockwork\Support\Slim\ClockworkMiddleware('/requests/storage/path')
//);

/* http://www.slimframework.com/docs/cookbook/enable-cors.html */
$app->options('/{routes:.+}', function ($request, $response, $args) { return $response; });

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

/* PROJECTS */
$app->get(    '/api/v1/projects',         'apiV1:getProjects'   );
$app->get(    '/api/v1/projects/{name}',  'apiV1:getProject'    );
$app->post(   '/api/v1/projects/{name}',  'apiV1:addProject'    );
$app->delete( '/api/v1/projects/{name}',  'apiV1:deleteProject' );

/* FEEDBACK */
$app->post(   '/api/v1/feedback',         'apiV1:addFeedback'   );
$app->get(    '/api/v1/feedback',         'apiV1:getFeedback'   );
$app->delete( '/api/v1/feedback/{id}',    'apiV1:deleteFeedback');

class apiV1 // TODO : Class per Object type w/ generic interface
{
    protected $ci;

    //Constructor
    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
        //to access items in the container... $this->ci->get('');
    }

    public function getProjects(Request $request, Response $response, $args) {

        try {
            $db = getDB();

//            $sth = $db->query("SELECT * FROM projects");
//            IMECWWW-START
            $sth = $db->query("SELECT * FROM projects_imecwww");
//            IMECWWW-END
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

    public function getProject(Request $request, Response $response, $args) {

        $projectName    = $args['name'];

        try {
            $db = getDB();

//            $sth = $db->prepare("SELECT * FROM projects WHERE projectName = :projectName");
            // IMECWWW-START
            $sth = $db->prepare("SELECT * FROM projects_imecwww WHERE projectName = :projectName");
            // IMECWWW-END
            $sth->execute([':projectName' => $projectName]);
            $res = $sth->fetch(PDO::FETCH_OBJ);

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

    /*
     * http://stackoverflow.com/questions/4976624/looping-through-all-the-properties-of-object-php
     * Whitelist loop through object properties
     *
     * http://www.restapitutorial.com/lessons/httpmethods.html
     * POST
     *  201
     *  404 Not found
     *  409 Conflict (already exists) ~
     * Should POST to /projects instead of /projects/:id?
     */
    public function addProject(Request $request, Response $response, $args) {

        $parsedBody = $request->getParsedBody();

        $projectName             = $args['name'];
        $description             = $parsedBody['description']               ; //? $parsedBody['description']                : NULL;
        $activity                = $parsedBody['activity']                  ; //? $parsedBody['activity']                   : NULL;
        $lastModified            = $parsedBody['lastModified']              ; //? $parsedBody['lastModified']               : NULL;
        $approvalStatus          = $parsedBody['approvalStatus']            ; //? $parsedBody['approvalStatus']             : NULL;
        $requester               = $parsedBody['requester']                 ; //? $parsedBody['requester']                  : NULL;
        $responsible             = $parsedBody['responsible']               ; //? $parsedBody['responsible']                : NULL;
        $FAB                     = $parsedBody['FAB']                       ; //? $parsedBody['FAB']                        : NULL;
        $designSupport           = $parsedBody['designSupport']             ; //? $parsedBody['designSupport']              : NULL;
        $preferredTimeOfDelivery = $parsedBody['preferredTimeOfDelivery']   ; //? $parsedBody['preferredTimeOfDelivery']    : NULL;
        $preferredMaskshop       = $parsedBody['preferredMaskshop']         ; //? $parsedBody['preferredMaskshop']          : NULL;
        $KP                      = $parsedBody['KP']                        ; //? $parsedBody['KP']                         : NULL;
        $KD                      = $parsedBody['KD']                        ; //? $parsedBody['KD']                         : NULL;
        $submitDate              = $parsedBody['submitDate']                ; //? $parsedBody['submitDate']                 : NULL;
        $approval1Date           = $parsedBody['approval1Date']             ; //? $parsedBody['approval1Date']              : NULL;
        $approval2Date           = $parsedBody['approval2Date']             ; //? $parsedBody['approval2Date']              : NULL;
        $rejectDate              = $parsedBody['rejectDate']                ; //? $parsedBody['rejectDate']                 : NULL;
        $typeOfWork              = $parsedBody['typeOfWork']                ; //? $parsedBody['typeOfWork']                 : NULL;

        try {
            $db = getDB();

//            $sth = $db->prepare("
//                INSERT INTO projects (projectName, description, activity, lastModified, approvalStatus, requester,
//                  responsible, FAB, designSupport, preferredTimeOfDelivery, preferredMaskshop, KP, KD, submitDate, approval1Date, approval2Date,
//                  rejectDate, typeOfWork)
//                VALUES (:projectName, :description, :activity, :lastModified, :approvalStatus, :requester,
//                  :responsible, :FAB, :designSupport, :preferredTimeOfDelivery, :preferredMaskshop, :KP, :KD, :submitDate, :approval1Date, :approval2Date,
//                  :rejectDate, :typeOfWork)
//                ON DUPLICATE KEY UPDATE
//                  description               = VALUES(description            ),
//                  activity                  = VALUES(activity               ),
//                  lastModified              = VALUES(lastModified           ),
//                  approvalStatus            = VALUES(approvalStatus         ),
//                  requester                 = VALUES(requester              ),
//                  responsible               = VALUES(responsible            ),
//                  FAB                       = VALUES(FAB                    ),
//                  designSupport             = VALUES(designSupport          ),
//                  preferredTimeOfDelivery   = VALUES(preferredTimeOfDelivery),
//                  preferredMaskshop         = VALUES(preferredMaskshop      ),
//                  KP                        = VALUES(KP                     ),
//                  KD                        = VALUES(KD                     ),
//                  submitDate                = VALUES(submitDate             ),
//                  approval1Date             = VALUES(approval1Date          ),
//                  approval2Date             = VALUES(approval2Date          ),
//                  rejectDate                = VALUES(rejectDate             ),
//                  typeOfWork                = VALUES(typeOfWork             )
//            ");
            // IMECWWW-START
            $sth = $db->prepare("
                INSERT INTO projects_imecwww (projectName, description, activity, lastModified, approvalStatus, requester,
                  responsible, FAB, designSupport, preferredTimeOfDelivery, preferredMaskshop, KP, KD, submitDate, approval1Date, approval2Date,
                  rejectDate, typeOfWork)
                VALUES (:projectName, :description, :activity, :lastModified, :approvalStatus, :requester,
                  :responsible, :FAB, :designSupport, :preferredTimeOfDelivery, :preferredMaskshop, :KP, :KD, :submitDate, :approval1Date, :approval2Date,
                  :rejectDate, :typeOfWork)
                ON DUPLICATE KEY UPDATE
                  description               = VALUES(description            ),
                  activity                  = VALUES(activity               ),
                  lastModified              = VALUES(lastModified           ),
                  approvalStatus            = VALUES(approvalStatus         ),
                  requester                 = VALUES(requester              ),
                  responsible               = VALUES(responsible            ),
                  FAB                       = VALUES(FAB                    ),
                  designSupport             = VALUES(designSupport          ),
                  preferredTimeOfDelivery   = VALUES(preferredTimeOfDelivery),
                  preferredMaskshop         = VALUES(preferredMaskshop      ),
                  KP                        = VALUES(KP                     ),
                  KD                        = VALUES(KD                     ),
                  submitDate                = VALUES(submitDate             ),
                  approval1Date             = VALUES(approval1Date          ),
                  approval2Date             = VALUES(approval2Date          ),
                  rejectDate                = VALUES(rejectDate             ),
                  typeOfWork                = VALUES(typeOfWork             )
            ");
            // IMECWWW-END

            $sth->execute([
                ':projectName'              => $projectName,
                ':description'              => $description,
                ':activity'                 => $activity,
                ':lastModified'             => $lastModified,
                ':approvalStatus'           => $approvalStatus,
                ':requester'                => $requester,
                ':responsible'              => $responsible,
                ':FAB'                      => $FAB,
                ':designSupport'            => $designSupport,
                ':preferredTimeOfDelivery'  => $preferredTimeOfDelivery,
                ':preferredMaskshop'        => $preferredMaskshop,
                ':KP'                       => $KP,
                ':KD'                       => $KD,
                ':submitDate'               => $submitDate,
                ':approval1Date'            => $approval1Date,
                ':approval2Date'            => $approval2Date,
                ':rejectDate'               => $rejectDate,
                ':typeOfWork'               => $typeOfWork,
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

    /*
     * DELETE
     *  200 OK
     *  404 Not Found (id not found or invalid)
     */
    function deleteProject(Request $request, Response $response, $args) {
        $projectName = $args['name'];

        try {
            $db = getDB();
//            $sth = $db->prepare("DELETE FROM projects WHERE projectName = :projectName");
            // IMECWWW-START
            $sth = $db->prepare("DELETE FROM projects_imecwww WHERE projectName = :projectName");
            // IMECWWW-END
            $sth->execute([':projectName' => $projectName]);

            if($sth) { // TODO Correct check and response (# of rows affected for example)
                $db = null;
                $response
                    ->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode($sth));
                return $response;
            } else {
                throw new PDOException('No records found.');
            }

        } catch (PDOException $e) {
            return $response->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->write('{"error":{"text":'. json_encode($e->getMessage()) .'}}');
        }
    }

    function addFeedback(Request $request, Response $response, $args) {

        $parsedBody = $request->getParsedBody();

        $userName    = $parsedBody['userName'];
        $submitDate  = date("c");
        $commentText = $parsedBody['commentText'];
        // TODO DEBUG : function appears to be called twice

        try {
            $db = getDB();

            $sth = $db->prepare("
                INSERT INTO feedback (userName, submitDate, commentText)
                VALUES (:userName, :submitDate, :commentText)
            ");
            $sth->execute([
                ':userName'                 => $userName,
                ':submitDate'               => $submitDate,
                ':commentText'              => $commentText
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

    function getFeedback(Request $request, Response $response, $args) {
        try {
            $db = getDB();

            $sth = $db->query("SELECT * FROM feedback");
            $res = $sth->fetchAll(PDO::FETCH_OBJ);

            $db = null;
            if($res) {
                $response
                    ->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode($res));
                return $response;
            } else {
                $response
                    ->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode([]));
                return $response;
            }
        } catch(PDOException $e) {
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->write('{"error":{"text":'. json_encode($e->getMessage()) .'}}');
        }
    }

    function deleteFeedback(Request $request, Response $response, $args) {
        $id = $args['id'];

        try {
            $db = getDB();
            $sth = $db->prepare("DELETE FROM feedback WHERE id = :id");
            $sth->execute([':id' => $id]);

            if($sth) { // TODO Correct check and response (# of rows affected for example)
                $db = null;
                $response
                    ->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode($sth));
                return $response;
            } else {
                throw new PDOException('No records found.');
            }

        } catch (PDOException $e) {
            return $response->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->write('{"error":{"text":'. json_encode($e->getMessage()) .'}}');
        }
    }
}

$app->run();