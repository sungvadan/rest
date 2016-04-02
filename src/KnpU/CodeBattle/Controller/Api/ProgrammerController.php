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
        $controllers->get ('/api/programmers/{nickname}', array($this, 'showAction'));
    }

    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(),true);
        $programmer = new Programmer($data['nickName'],$data['avatarNumber']);
        $programmer->tagLine = $data['tagLine'];
        $programmer->userId = $this->findUserByUsername('weaverryan')->id;
        $this->save($programmer);
        $respone = new Response('It\'s worked',201);
        $respone->headers->set('Location', 'some/programmer/url');
        return $respone;
    }

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);
        if(!$programmer){
            $this->throw404('Not Found');
        }
        $data = array(
            'nickname' => $programmer->nickname,
            'avatarNumber' => $programmer->avatarNumber,
            'powerLevel' => $programmer->powerLevel,
            'tagLine' => $programmer->tagLine,
        );
        $response = new Response(json_encode($data), 200);
        $response->headers->set('Content-type','application/json');
        return $response;
    }

}
