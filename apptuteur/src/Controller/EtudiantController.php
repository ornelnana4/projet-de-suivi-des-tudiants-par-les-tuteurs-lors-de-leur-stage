<?php

namespace App\Controller;

use App\Form\EtudiantType;
use App\Entity\Etudiant;
use App\Repository\EtudiantRepository;
use App\Repository\TuteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EtudiantController extends AbstractController
{
    #[Route('/etudiants', name: 'app_etudiant_index')]
    public function index(
        SessionInterface $session,
        TuteurRepository $tuteurRepository,
        EtudiantRepository $etudiantRepository
    ): Response {
        $tuteurId = $session->get('tuteur_id');

        if (!$tuteurId) {
            $this->addFlash('error', 'Vous devez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        $tuteur = $tuteurRepository->find($tuteurId);

        if (!$tuteur) {
            $this->addFlash('error', 'Tuteur introuvable.');
            return $this->redirectToRoute('app_login');
        }

        // Tous les étudiants liés à ce tuteur
        $etudiants = $etudiantRepository->findBy(['tuteur' => $tuteur]);

        return $this->render('etudiant/index.html.twig', [
            'tuteur' => $tuteur,
            'etudiants' => $etudiants,
        ]);
    }

    #[Route('/etudiants/new', name: 'app_etudiant_new')]
    public function new(
        Request $request,
        SessionInterface $session,
        TuteurRepository $tuteurRepository,
        EntityManagerInterface $em
    ): Response {
        $tuteurId = $session->get('tuteur_id');

        if (!$tuteurId) {
            $this->addFlash('error', 'Vous devez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        $tuteur = $tuteurRepository->find($tuteurId);

        if (!$tuteur) {
            $this->addFlash('error', 'Tuteur introuvable.');
            return $this->redirectToRoute('app_login');
        }

        $etudiant = new Etudiant();
        $etudiant->setTuteur($tuteur); // Lier automatiquement au tuteur connecté

        if ($request->isMethod('POST')) {
            $etudiant->setNom($request->request->get('nom'));
            $etudiant->setPrenom($request->request->get('prenom'));
            $etudiant->setFormation($request->request->get('formation'));

            $em->persist($etudiant);
            $em->flush();

            $this->addFlash('success', 'Étudiant ajouté avec succès.');
            return $this->redirectToRoute('app_etudiant_index');
        }

        return $this->render('etudiant/new.html.twig', [
            'tuteur' => $tuteur,
        ]);
    }
    #[Route('/etudiants/{id}/edit', name: 'app_etudiant_edit')]
    public function edit(
        int $id,
        Request $request,
        SessionInterface $session,
        TuteurRepository $tuteurRepository,
        EtudiantRepository $etudiantRepository,
        EntityManagerInterface $em
    ): Response {
        $tuteurId = $session->get('tuteur_id');
        if (!$tuteurId) {
            $this->addFlash('error', 'Vous devez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        $tuteur = $tuteurRepository->find($tuteurId);
        if (!$tuteur) {
            $this->addFlash('error', 'Tuteur introuvable.');
            return $this->redirectToRoute('app_login');
        }

        $etudiant = $etudiantRepository->find($id);

        // Vérifier qu’il appartient bien au tuteur connecté (5.3)
        if (!$etudiant || $etudiant->getTuteur() !== $tuteur) {
            $this->addFlash('error', 'Accès non autorisé à cet étudiant.');
            return $this->redirectToRoute('app_etudiant_index');
        }

        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Étudiant modifié avec succès.');
            return $this->redirectToRoute('app_etudiant_index');
        }

        return $this->render('etudiant/edit.html.twig', [
            'tuteur' => $tuteur,
            'form' => $form->createView(),
            'etudiant' => $etudiant,
        ]);
    }
}
