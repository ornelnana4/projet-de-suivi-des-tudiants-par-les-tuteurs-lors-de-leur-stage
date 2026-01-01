<?php

namespace App\Controller;

use App\Entity\Visite;
use App\Entity\Etudiant;
use App\Entity\Tuteur;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\VisiteRepository;
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
    public function list(
        Etudiant $etudiant,
        Request $request,
        VisiteRepository $visiteRepository,
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response {
        $tuteurId = $session->get('tuteur_id');
        if (!$tuteurId) {
            return $this->redirectToRoute('login');
        }

        $tuteur = $em->getRepository(Tuteur::class)->find($tuteurId);

        // Récupération des filtres GET
        $statut = $request->query->get('statut');        // null, 'prévue', 'réalisée', 'annulée'
        $ordre  = $request->query->get('ordre', 'desc'); // 'asc' ou 'desc'

        // Utilisation du repository personnalisé
        $visites = $visiteRepository->findByEtudiantWithFilters($etudiant, $statut, $ordre);

        return $this->render('visite/list.html.twig', [
            'etudiant' => $etudiant,
            'visites'  => $visites,
            'tuteur'   => $tuteur,
            'statut'   => $statut,
            'ordre'    => $ordre,
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
    #[Route('/visites/{id}/compte-rendu', name: 'app_visite_compte_rendu', methods: ['GET', 'POST'])]
    public function compteRendu(
        Visite $visite,
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response {
        $tuteurId = $session->get('tuteur_id');
        if (!$tuteurId) {
            return $this->redirectToRoute('login');
        }

        // On ne change que le champ compteRendu
        if ($request->isMethod('POST')) {
            $visite->setCompteRendu($request->request->get('compteRendu'));
            $em->flush();

            $this->addFlash('success', 'Compte-rendu mis à jour.');
            return $this->redirectToRoute('app_visite_compte_rendu', ['id' => $visite->getId()]);
        }

        $etudiant = $visite->getEtudiant();
        $tuteur   = $em->getRepository(Tuteur::class)->find($tuteurId);

        return $this->render('visite/compte_rendu.html.twig', [
            'visite'   => $visite,
            'etudiant' => $etudiant,
            'tuteur'   => $tuteur,
        ]);
    }

    #[Route('/visites/{id}/compte-rendu/pdf', name: 'app_visite_compte_rendu_pdf', methods: ['GET'])]
    public function compteRenduPdf(Visite $visite): Response
    {
        $etudiant = $visite->getEtudiant();

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($pdfOptions);

        // HTML du PDF à partir d’un template Twig dédié
        $html = $this->renderView('visite/compte_rendu_pdf.html.twig', [
            'visite'   => $visite,
            'etudiant' => $etudiant,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="compte-rendu-visite-' . $visite->getId() . '.pdf"',
            ]
        );
    }

    
}
