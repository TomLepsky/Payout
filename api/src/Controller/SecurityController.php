<?php
namespace App\Controller;

use App\Config;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route(path: '/api/logout', methods: ['GET'])]
    public function logout(Request $request) : Response
    {
        $response = (new Response())->prepare($request);
        $response->headers->clearCookie(Config::JWT_COOKIE_NAME);

        return $response->setStatusCode(204);
    }

    #[Route(path: '/api/check-auth', methods: ['GET'])]
    public function checkAuth(Request $request) : Response
    {
        $this->denyAccessUnlessGranted(Config::AUTHENTICATED);
        return (new Response())->prepare($request)->setStatusCode(204);
    }
}
