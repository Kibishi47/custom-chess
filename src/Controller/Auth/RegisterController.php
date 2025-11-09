<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Service\Normalizer\ValidationErrorNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
        private ValidatorInterface $validator,
        private JWTTokenManagerInterface $jwt,
        private ValidationErrorNormalizer $validationErrorNormalizer
    ) {}

    #[Route('/api/register', name: 'api_register', methods: ['POST'], format: 'json')]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '[]', true);
        $email    = trim((string)($data['email'] ?? ''));
        $username = trim((string)($data['username'] ?? ''));
        $password = (string)($data['password'] ?? '');

        $user = (new User())
            ->setEmail($email)
            ->setUsername($username);

        // Validation (group "create" pour longueur mini du mot de passe)
        $user->setPassword($password);
        $violations = $this->validator->validate($user, null, ['register']);
        if (count($violations) > 0) {
            return $this->validationErrorNormalizer->buildErrorResponse($violations);
        }

        $user->setPassword($this->hasher->hashPassword($user, $password));
        $this->em->persist($user);
        $this->em->flush();

        // Génère et renvoie le token
        $token = $this->jwt->create($user);

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
            ],
        ], 201);
    }
}
