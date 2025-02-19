<?php

namespace App\Controller;

use App\Entity\Depense;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

final class DepenseController extends AbstractController
{
    #[Route('/api/depense', name: 'add_depense', methods: ['POST'])]
    public function addDepense(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return new Response('User not authenticated', 401);
        }

        // Récupérer les données envoyées dans le corps de la requête
        $data = json_decode($request->getContent(), true);
        
        // Valider les données nécessaires
        if (empty($data['montant']) || empty($data['categorie']) || empty($data['date'])) {
            return new Response('Missing required fields', 400);
        }

        // Créer et remplir l'entité Depense
        $depense = new Depense();
        $depense->setMontant($data['montant']);
        $depense->setCategorie($data['categorie']);
        $depense->setDate(new \DateTime($data['date']));
        $depense->setCommentaire($data['commentaire'] ?? null);

        // Associer l'utilisateur connecté à la dépense
        $depense->setUser($user);

        // Persister la dépense dans la base de données
        $entityManager->persist($depense);
        $entityManager->flush();

        // Retourner une réponse avec les informations de la dépense
        return $this->json([
            'message' => 'Dépense ajoutée avec succès',
            'data' => [
                'montant' => $depense->getMontant(),
                'categorie' => $depense->getCategorie(),
                'date' => $depense->getDate()->format('Y-m-d'),
                'commentaire' => $depense->getCommentaire(),
            ]
        ], 201);
    }

    #[Route('/api/depenses', name: 'get_depenses', methods: ['GET'])]
    public function getDepenses(EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur authentifié
        $user = $this->getUser();
        if (!$user) {
            return new Response('User not authenticated', 401);
        }

        // Récupérer les dépenses de l'utilisateur
        $depenses = $entityManager->getRepository(Depense::class)->findBy(['user' => $user]);

        // Retourner les dépenses sous forme de JSON
        return $this->json($depenses);
    }
}
