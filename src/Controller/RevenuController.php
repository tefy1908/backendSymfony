<?php

namespace App\Controller;

use App\Entity\Revenu;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class RevenuController extends AbstractController
{
    #[Route('/api/revenu', name: 'add_revenu', methods: ['POST'])]
    public function addRevenu(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); 
        
        // Si aucun utilisateur n'est connecté, retourner une erreur 401
        if (!$user) {
            return new Response('User not authenticated', 401);
        }

        $data = json_decode($request->getContent(), true);
        
        // Créer un nouvel objet Revenu
        $revenu = new Revenu();
        $revenu->setMontant($data['montant']);
        $revenu->setCategorie($data['categorie']);
        $revenu->setDate(new \DateTime($data['date']));
        $revenu->setCommentaire($data['commentaire'] ?? null);

        // Assigner l'utilisateur connecté au revenu
        $revenu->setUser($user);  // L'utilisateur connecté est un objet de type User, donc c'est correct ici
        
        // Persister le revenu et l'enregistrer
        $entityManager->persist($revenu);
        $entityManager->flush();

        return $this->json(['message' => 'Revenu ajouté avec succès'], 201);
    }

    #[Route('/api/revenus', name: 'get_revenus', methods: ['GET'])]
    public function getRevenus(EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur authentifié
        $user = $this->getUser(); 

        // Si aucun utilisateur n'est connecté, retourner une erreur 401
        if (!$user) {
            return new Response('User not authenticated', 401);
        }

        // Récupérer les revenus associés à l'utilisateur
        $revenus = $entityManager->getRepository(Revenu::class)->findBy(['user' => $user]);

        // Retourner les revenus sous forme de JSON
        return $this->json($revenus);
    }
}
