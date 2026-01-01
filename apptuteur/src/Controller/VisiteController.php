<?php

namespace App\Controller;

use App\Entity\Visite;
use App\Entity\Etudiant;
use App\Entity\Tuteur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class VisiteController extends AbstractController
{
    // 6.1 Liste des visites d’un étudiant
    #[Route('/etudiants/{id}/visites', name: 'app_etudiant_visites')]
    public function list(Etudiant $etudiant, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $tuteurId = $session->get('tuteur_id');
        if (!$tuteurId) {
            return $this->redirectToRoute('login');
        }

        $tuteur = $em->getRepository(Tuteur::class)->find($tuteurId);

        $visites = $em->getRepository(Visite::class)->findBy(
            ['etudiant' => $etudiant],
            ['date' => 'DESC']
        );

        return $this->render('visite/list.html.twig', [
            'etudiant' => $etudiant,
            'visites'  => $visites,
            'tuteur'   => $tuteur,
        ]);
    }

    // 6.2 Ajouter une visite pour un étudiant
    #[Route('/etudiants/{id}/visites/new', name: 'app_visite_new')]
    public function new(Etudiant $etudiant, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $tuteurId = $session->get('tuteur_id');
        if (!$tuteurId) {
            return $this->redirectToRoute('login');
        }

        // Tuteur connecté
        $tuteur = $em->getRepository(Tuteur::class)->find($tuteurId);

        $visite = new Visite();

        // Préremplissage demandé
        $visite->setEtudiant($etudiant);
        $visite->setTuteur($tuteur);
        $visite->setStatut('prévue');

        if ($request->isMethod('POST')) {
            $visite->setDate(new \DateTimeImmutable($request->request->get('date')));
            $visite->setCommentaire($request->request->get('commentaire'));
            $visite->setCompteRendu($request->request->get('compteRendu'));
            $visite->setStatut($request->request->get('statut') ?: 'prévue');

            $em->persist($visite);
            $em->flush();

            return $this->redirectToRoute('app_etudiant_visites', [
                'id' => $etudiant->getId(),
            ]);
        }

        return $this->render('visite/new.html.twig', [
            'etudiant' => $etudiant,
            'visite'   => $visite,
            'tuteur'   => $tuteur,
        ]);
    }

    // 6.3 Modifier une visite
    #[Route('/visites/{id}/edit', name: 'app_visite_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $tuteurId = $session->get('tuteur_id');
        if (!$tuteurId) {
            return $this->redirectToRoute('login');
        }

        $tuteur = $em->getRepository(Tuteur::class)->find($tuteurId);

        $visite = $em->getRepository(Visite::class)->find($id);
        if (!$visite) {
            return $this->redirectToRoute('dashboard');
        }

        if ($request->isMethod('POST')) {
            $visite->setDate(new \DateTimeImmutable($request->request->get('date')));
            $visite->setCommentaire($request->request->get('commentaire'));
            $visite->setCompteRendu($request->request->get('compteRendu'));
            $visite->setStatut($request->request->get('statut'));

            $em->flush();

            return $this->redirectToRoute('app_etudiant_visites', [
                'id' => $visite->getEtudiant()->getId(),
            ]);
        }

        return $this->render('visite/edit.html.twig', [
            'visite'   => $visite,
            'etudiant' => $visite->getEtudiant(),
            'tuteur'   => $tuteur,
        ]);
    }

    #[Route('/visites/delete/{id}', name: 'visites_delete')]
    public function delete(int $id, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $tuteurId = $session->get('tuteur_id');
        if (!$tuteurId) {
            return $this->redirectToRoute('login');
        }

        $visite = $em->getRepository(Visite::class)->find($id);
        if ($visite) {
            $etudiant = $visite->getEtudiant();
            $em->remove($visite);
            $em->flush();

            return $this->redirectToRoute('app_etudiant_visites', [
                'id' => $etudiant->getId(),
            ]);
        }

        return $this->redirectToRoute('dashboard');
    }
}
