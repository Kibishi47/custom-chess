<?php

namespace App\Service\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private JWTTokenManagerInterface $tokenManager
    ) {}

    public function getUserFromBodyRequest(Request $request): User
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['token'])) {
            throw new \Exception('Invalid payload', 400);
        }

        $token = $data['token'];

        $jwtData = $this->tokenManager->parse($token);
        $username = $jwtData['username'];
        if (!$user = $this->userRepository->findOneBy(['username' => $username])) {
            throw new \Exception('Invalid token user', 401);
        }

        return $user;
    }
}
