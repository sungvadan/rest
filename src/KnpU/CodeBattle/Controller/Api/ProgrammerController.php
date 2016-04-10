<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Controller\BaseController;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use KnpU\CodeBattle\Model\Programmer;

class ProgrammerController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', array($this, 'newAction'));
        $controllers->get('/api/programmers', array($this, 'listAction'));
        $controllers->get('/api/programmers/{nickname}', array($this, 'showAction'))
            ->bind('api_programmers_show');
        $controllers->put('/api/programmers/{nickname}', array($this, 'updateAction'))
            ->bind('api_programmers_update');
    }

    public function newAction(Request $request)
    {
        $programmer = new Programmer();
        $this->handleRequest($request, $programmer);
        $data = $this->serializeProgrammer($programmer);
        $response = new JsonResponse($data, 201);
        $url = $this->generateUrl('api_programmers_show', ['nickname' => $programmer->nickname]);
        $response->headers->set('Location', $url);
        return $response;
    }

    public function updateAction(Request $request, $nickname)
    {
        # throw new \Exception('you code somthing wrong');
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);
        if (!$programmer) {
            $this->throw404('Not Found');
        }
        $this->handleRequest($request, $programmer);
        $data = $this->serializeProgrammer($programmer);
        $response = new JsonResponse($data, 200);
        return $response;
    }

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);
        if (!$programmer) {
            $this->throw404('Not Found');
        }
        $data = array(
            'nickname' => $programmer->nickname,
            'avatarNumber' => $programmer->avatarNumber,
            'powerLevel' => $programmer->powerLevel,
            'tagLine' => $programmer->tagLine,
        );
        $response = new JsonResponse($data, 200);
        return $response;
    }

    public function listAction()
    {
        $programmers = $this->getProgrammerRepository()->findAll();
        $data = array('programmers' => array());
        foreach ($programmers as $programmer) {
            $data['programmers'][] = $this->serializeProgrammer($programmer);
        }
        $response = new JsonResponse($data, 200);
        $response->headers->set('Content-type', 'application/json');
        return $response;
    }

    private function serializeProgrammer($programmer)
    {
        $data = array(
            'nickname' => $programmer->nickname,
            'avatarNumber' => $programmer->avatarNumber,
            'powerLevel' => $programmer->powerLevel,
            'tagLine' => $programmer->tagLine,
        );
        return $data;
    }

    private function handleRequest(Request $request, Programmer $programmer)
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            throw new \Exception(sprintf('Invalid JSON: '.$request->getContent()));
        }

        // determine which properties should be changeable on this request
        $apiProperties = array('nickname', 'avatarNumber', 'tagLine');

        // update the properties
        foreach ($apiProperties as $property) {
            $val = isset($data[$property]) ? $data[$property] : null;
            $programmer->$property = $val;
        }

        $programmer->userId = $this->findUserByUsername('weaverryan')->id;

        $this->save($programmer);
    }

}
