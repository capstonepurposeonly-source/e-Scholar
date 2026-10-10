<?php

namespace App\Form;

use App\Entity\Student;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

class StudentRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Scholarship Information
            ->add('studentNumber', TextType::class, [
                'label' => 'Student Number',
                'attr' => ['placeholder' => 'e.g., 2024-12345']
            ])
            ->add('scholarshipType', ChoiceType::class, [
                'label' => 'Scholarship Type',
                'choices' => [
                    'ACEF-GIAHEP' => 'ACEF-GIAHEP',
                    'CMSP' => 'CMSP',
                    'COSCHO' => 'COSCHO',
                    'Landbank' => 'Landbank',
                    'NGCP' => 'NGCP',
                    'SIDA-SGP' => 'SIDA-SGP',
                    'TDP' => 'TDP',
                    'TES' => 'TES',
                    'PLDT' => 'PLDT',
                ],
                'placeholder' => 'Select scholarship type'
            ])

            // Personal Information
            ->add('firstName', TextType::class, [
                'label' => 'First Name'
            ])
            ->add('middleName', TextType::class, [
                'label' => 'Middle Name',
                'required' => false
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name'
            ])
            ->add('extensionName', TextType::class, [
                'label' => 'Extension Name',
                'required' => false,
                'attr' => ['placeholder' => 'e.g., Jr., Sr., III']
            ])
            ->add('dateOfBirth', DateType::class, [
                'label' => 'Date of Birth',
                'widget' => 'single_text'
            ])
            ->add('placeOfBirth', TextType::class, [
                'label' => 'Place of Birth'
            ])
            ->add('sex', ChoiceType::class, [
                'label' => 'Sex',
                'choices' => [
                    'Male' => 'Male',
                    'Female' => 'Female'
                ]
            ])
            ->add('civilStatus', ChoiceType::class, [
                'label' => 'Civil Status',
                'choices' => [
                    'Single' => 'Single',
                    'Married' => 'Married',
                    'Widowed' => 'Widowed',
                    'Separated' => 'Separated'
                ]
            ])
            ->add('religion', TextType::class, [
                'label' => 'Religion',
                'required' => false
            ])

            // Academic Information
            ->add('campus', ChoiceType::class, [
                'label' => 'Campus',
                'choices' => [
                    'Main Campus 1' => 'Main Campus 1',
                    'Main Campus 2' => 'Main Campus 2',
                    'Guihulngan' => 'Guihulngan',
                    'Bais' => 'Bais',
                    'Pamplona' => 'Pamplona',
                    'Mabinay' => 'Mabinay',
                    'Bayawan' => 'Bayawan'
                ],
                'placeholder' => 'Select campus'
            ])
            ->add('course', TextType::class, [
                'label' => 'Course',
                'attr' => ['placeholder' => 'e.g., BS Computer Science']
            ])
            ->add('yearLevel', ChoiceType::class, [
                'label' => 'Year Level',
                'choices' => [
                    '1st Year' => '1st Year',
                    '2nd Year' => '2nd Year',
                    '3rd Year' => '3rd Year',
                    '4th Year' => '4th Year'
                ]
            ])
            ->add('gwa', TextType::class, [
                'label' => 'General Weighted Average (GWA)',
                'required' => false,
                'attr' => ['placeholder' => 'e.g., 1.75']
            ])
            ->add('semester', ChoiceType::class, [
                'label' => 'Current Semester',
                'choices' => [
                    '1st Semester' => '1st Semester',
                    '2nd Semester' => '2nd Semester',
                    'Summer' => 'Summer'
                ]
            ])
            ->add('academicYear', TextType::class, [
                'label' => 'Academic Year',
                'attr' => ['placeholder' => 'e.g., 2024-2025']
            ])
            ->add('highSchool', TextType::class, [
                'label' => 'High School Graduated',
                'required' => false
            ])
            ->add('highSchoolYear', TextType::class, [
                'label' => 'High School Year Graduated',
                'required' => false,
                'attr' => ['placeholder' => 'e.g., 2020']
            ])

            // Contact Information
            ->add('email', EmailType::class, [
                'label' => 'Email Address'
            ])
            ->add('contactNumber', TelType::class, [
                'label' => 'Contact Number',
                'attr' => ['placeholder' => 'e.g., 09123456789']
            ])
            ->add('alternateContactNumber', TelType::class, [
                'label' => 'Alternate Contact Number',
                'required' => false,
                'attr' => ['placeholder' => 'e.g., 09123456789']
            ])

            // Address Information
            ->add('permanentAddress', TextareaType::class, [
                'label' => 'Permanent Address',
                'attr' => ['rows' => 2]
            ])
            ->add('currentAddress', TextareaType::class, [
                'label' => 'Current Address',
                'required' => false,
                'attr' => ['rows' => 2, 'placeholder' => 'Leave blank if same as permanent address']
            ])
            ->add('barangay', TextType::class, [
                'label' => 'Barangay'
            ])
            ->add('municipality', TextType::class, [
                'label' => 'Municipality/City'
            ])
            ->add('province', TextType::class, [
                'label' => 'Province'
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'Zip Code',
                'required' => false
            ])

            // Family Information
            ->add('fatherName', TextType::class, [
                'label' => "Father's Name",
                'required' => false
            ])
            ->add('fatherOccupation', TextType::class, [
                'label' => "Father's Occupation",
                'required' => false
            ])
            ->add('fatherContactNumber', TelType::class, [
                'label' => "Father's Contact Number",
                'required' => false
            ])
            ->add('motherName', TextType::class, [
                'label' => "Mother's Name",
                'required' => false
            ])
            ->add('motherOccupation', TextType::class, [
                'label' => "Mother's Occupation",
                'required' => false
            ])
            ->add('motherContactNumber', TelType::class, [
                'label' => "Mother's Contact Number",
                'required' => false
            ])
            ->add('guardianName', TextType::class, [
                'label' => "Guardian's Name (if applicable)",
                'required' => false
            ])
            ->add('guardianRelationship', TextType::class, [
                'label' => 'Relationship to Guardian',
                'required' => false
            ])
            ->add('guardianContactNumber', TelType::class, [
                'label' => "Guardian's Contact Number",
                'required' => false
            ])
            ->add('numberOfSiblings', TextType::class, [
                'label' => 'Number of Siblings',
                'required' => false,
                'attr' => ['placeholder' => 'e.g., 2']
            ])
            ->add('familyMonthlyIncome', TextType::class, [
                'label' => 'Family Monthly Income',
                'required' => false,
                'attr' => ['placeholder' => 'e.g., 15000']
            ])

            // Profile Picture
            ->add('profilePicture', FileType::class, [
                'label' => 'Profile Picture (2x2 ID Photo)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/jpg'],
                        mimeTypesMessage: 'Please upload a valid image (JPG or PNG)'
                    )
                ],
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/jpg'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Student::class,
        ]);
    }
}
