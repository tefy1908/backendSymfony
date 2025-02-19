<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class UserController extends AbstractController
{
    #[Route('/api/register', name: 'user_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les données JSON de la requête
        $data = json_decode($request->getContent(), true);
        
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $username = $data['username'] ?? null; // Ajouter la récupération du username
    
        // Vérifier que les données sont présentes
        if (!$email || !$password || !$username) {
            return new JsonResponse(['error' => 'Email, username, and password are required'], 400);
        }
    
        // Créer un nouvel utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username); // Assigner le username
    
        // Hacher le mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
    
        // Sauvegarder l'utilisateur en base de données
        $entityManager->persist($user);
        $entityManager->flush();
    
        return new JsonResponse(['message' => 'User successfully registered'], 201);
    }

    // Route de connexion 
    #[Route('/api/login', name:'user_login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $username = $data['username'] ?? null; // Ajouter la récupération du username


        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email and password are required'], 400);
        }

        // Trouver l'utilisateur dans la base de données
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['error' => 'Invalid email or password'], 401);
        }

        // Vérifier si le mot de passe est valide
        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Invalid email or password'], 401);
        }

        // Générer un token JWT
        $token = $JWTManager->create($user);

        // Retourner le token JWT dans la réponse
        return new JsonResponse(['token' => $token], 200);
    }
    #[Route('/api/logout', name: 'user_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return new JsonResponse(['message' => 'Déconnexion réussie'], 200);
    }
    #[Route('/api/user', name: 'user_info', methods: ['GET'])]
    public function getUserInfo(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }
        
        return new JsonResponse([
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
        ], 200);
    }
}