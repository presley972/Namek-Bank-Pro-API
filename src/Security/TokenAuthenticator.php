<?php
/**
 * Created by PhpStorm.
 * User: beren
 * Date: 24/08/2018
 * Time: 10:19
 */

namespace App\Security;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{

    public function supports(Request $request)
    {
        return $request->headers->has('AUTH-TOKEN');
    }

    public function getCredentials(Request $request)
    {
        return array(
            'token' => $request->headers->get('AUTH-TOKEN'),
        );

    }

    public function getUser($credentials, UserProviderInterface $userProvider){
        $apiKey = $credentials['token'];
        if (null === $apiKey) {
            return;
        }
// if a User object, checkCredentials() is called
        return $userProvider->loadUserByUsername($apiKey);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
// no credential check is needed in this case
// return true to cause authentication success
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception ->getMessageKey(), $exception ->getMessageData())
        );
        return new JsonResponse( $data, Response:: HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            'message' => 'Authentication Required'
        );
        return new JsonResponse( $data, Response:: HTTP_FORBIDDEN);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}