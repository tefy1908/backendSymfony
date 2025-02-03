<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;


class UserController extends AbstractController
{
    #[Route('/user_register', name: 'user_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les données JSON de la requête
        $data = json_decode($request->getContent(), true);
        
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        // Vérifier que les données sont présentes
        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email and password are required'], 400);
        }

        // Créer un nouvel utilisateur
        $user = new User();
        $user->setEmail($email);

        // Hacher le mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Sauvegarder l'utilisateur en base de données
        $entityManager->persist($user);
        $entityManager->flush();

        // Réponse avec un succès
        return new JsonResponse(['message' => 'User successfully registered'], 201);
    }

    // Route de connexion 
    #[Route('user_login', name:'user_login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les données de la requête (email et mot de passe)
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        // Vérifier que les champs sont présents
        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email and password are required'], 400);
        }

        // Trouver l'utilisateur dans la base de données
        $user = $entityManager->getRepository(User::class)->findOneBy(['Email' => $email]);

        // Si l'utilisateur n'existe pas
        if (!$user) {
            return new JsonResponse(['error' => 'Invalid email or password'], 401);
        }

        // Vérifier si le mot de passe est correct
        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Invalid email or password'], 401);
        }

        // Si tout est correct, renvoyer une réponse de succès
        return new JsonResponse(['message' => 'User successfully logged in'], 200);
    }
}