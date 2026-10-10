<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentRegistrationType;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class StudentController extends AbstractController
{
    #[Route('/student/register', name: 'student_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $student = new Student();
        $form = $this->createForm(StudentRegistrationType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle profile picture upload
            $profilePictureFile = $form->get('profilePicture')->getData();

            if ($profilePictureFile) {
                $originalFilename = pathinfo($profilePictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePictureFile->guessExtension();

                try {
                    $profilePictureFile->move(
                        $this->getParameter('profile_pictures_directory'),
                        $newFilename
                    );
                    $student->setProfilePicturePath($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'There was an error uploading your profile picture.');
                }
            }

            // Set registration status to pending
            $student->setRegistrationStatus('Pending');

            $entityManager->persist($student);
            $entityManager->flush();

            $this->addFlash('success', 'Your scholarship application has been submitted successfully! Your application is now pending review.');

            return $this->redirectToRoute('student_register');
        }

        return $this->render('student/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/student/track/{studentNumber}', name: 'student_track')]
    public function track(string $studentNumber, StudentRepository $studentRepository): Response
    {
        $student = $studentRepository->findOneBy(['studentNumber' => $studentNumber]);

        if (!$student) {
            $this->addFlash('error', 'Student number not found.');
            return $this->redirectToRoute('student_track_search');
        }

        return $this->render('student/track.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/student/track', name: 'student_track_search')]
    public function trackSearch(): Response
    {
        return $this->render('student/track_search.html.twig');
    }
}
