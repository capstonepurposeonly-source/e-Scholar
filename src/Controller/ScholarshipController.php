<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScholarshipController extends AbstractController
{
    #[Route('/scholarship/{slug}', name: 'app_scholarship_detail')]
    public function detail(string $slug): Response
    {
        // Scholarship data - you'll provide the details for each
        $scholarships = [
            'acef-giahep' => [
                'name' => 'ACEF-GIAHEP Scholarship',
                'fullName' => 'Agricultural Competitiveness Enhancement Fund - Global Initiatives for Agricultural Higher Education Program',
                'description' => 'This scholarship program supports students pursuing agricultural sciences and related fields.',
                'logo' => 'ACEF-GIAHEP LOGO-modified.png',
                'requirements' => [
                    'Must be enrolled in Agriculture-related programs',
                    'Minimum GPA of 2.5',
                    'Submit Letter of Intent',
                    'Submit Recommendation Letter from Faculty'
                ],
                'benefits' => [
                    'Full tuition coverage',
                    'Book allowance',
                    'Monthly stipend',
                    'Research funding'
                ],
                'applicationProcess' => 'Applications are open from January to March each year. Submit requirements to the Scholarship Office.',
                'contact' => 'scholarship@norsu.edu.ph'
            ],
            'cmsp' => [
                'name' => 'CMSP Scholarship',
                'fullName' => 'Commission on Higher Education Merit Scholarship Program',
                'description' => 'Merit-based scholarship for outstanding students.',
                'logo' => 'CMSP-modified.png',
                'requirements' => [],
                'benefits' => [],
                'applicationProcess' => 'Details will be provided.',
                'contact' => 'scholarship@norsu.edu.ph'
            ],
            'coscho' => [
                'name' => 'COSCHO Scholarship',
                'fullName' => 'Congressional Scholarship',
                'description' => 'Scholarship funded by congressional representatives.',
                'logo' => 'COSCHO LOGO-modified.png',
                'requirements' => [],
                'benefits' => [],
                'applicationProcess' => 'Details will be provided.',
                'contact' => 'scholarship@norsu.edu.ph'
            ],
            'landbank' => [
                'name' => 'Land Bank Scholarship',
                'fullName' => 'Land Bank of the Philippines Educational Assistance',
                'description' => 'Financial assistance from Land Bank of the Philippines.',
                'logo' => 'LANDBANK LOGO-modified.png',
                'requirements' => [],
                'benefits' => [],
                'applicationProcess' => 'Details will be provided.',
                'contact' => 'scholarship@norsu.edu.ph'
            ],
            'ngcp' => [
                'name' => 'NGCP Scholarship',
                'fullName' => 'National Grid Corporation of the Philippines Scholarship',
                'description' => 'Engineering and technical scholarship program.',
                'logo' => 'NGCP LOGO-modified.png',
                'requirements' => [],
                'benefits' => [],
                'applicationProcess' => 'Details will be provided.',
                'contact' => 'scholarship@norsu.edu.ph'
            ],
            'sida-sgp' => [
                'name' => 'SIDA-SGP Scholarship',
                'fullName' => 'Student Initiative for Development Assistance - Student Grant Program',
                'description' => 'Support for students with financial need.',
                'logo' => 'SIDA-SGP LOGO-modified.png',
                'requirements' => [],
                'benefits' => [],
                'applicationProcess' => 'Details will be provided.',
                'contact' => 'scholarship@norsu.edu.ph'
            ],
            'tdp' => [
                'name' => 'TDP Scholarship',
                'fullName' => 'Tulong Dunong Program',
                'description' => 'Government assistance program for deserving students.',
                'logo' => 'TDP logo-modified.png',
                'requirements' => [],
                'benefits' => [],
                'applicationProcess' => 'Details will be provided.',
                'contact' => 'scholarship@norsu.edu.ph'
            ],
            'tes' => [
                'name' => 'TES Scholarship',
                'fullName' => 'Tertiary Education Subsidy',
                'description' => 'Government subsidy for tertiary education.',
                'logo' => 'TES LOGO-modified.png',
                'requirements' => [],
                'benefits' => [],
                'applicationProcess' => 'Details will be provided.',
                'contact' => 'scholarship@norsu.edu.ph'
            ],
            'pldt' => [
                'name' => 'PLDT Smart Foundation Scholarship',
                'fullName' => 'PLDT Smart Foundation ICT Scholarship Program',
                'description' => 'Scholarship program for ICT and telecommunications students.',
                'logo' => 'PLDT-Logo.png',
                'requirements' => [],
                'benefits' => [],
                'applicationProcess' => 'Details will be provided.',
                'contact' => 'scholarship@norsu.edu.ph'
            ],
        ];

        if (!isset($scholarships[$slug])) {
            throw $this->createNotFoundException('Scholarship not found');
        }

        return $this->render('scholarship/detail.html.twig', [
            'scholarship' => $scholarships[$slug],
            'slug' => $slug
        ]);
    }
}
