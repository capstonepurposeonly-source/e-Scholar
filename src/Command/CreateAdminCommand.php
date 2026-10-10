<?php

namespace App\Command;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create a new admin user',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask('Email address', 'admin@norsu.edu.ph');
        $fullName = $io->ask('Full name', 'NORSU Administrator');
        $password = $io->askHidden('Password (hidden)');

        if (!$password) {
            $io->error('Password cannot be empty!');
            return Command::FAILURE;
        }

        // Check if admin already exists
        $existingAdmin = $this->entityManager
            ->getRepository(Admin::class)
            ->findOneBy(['email' => $email]);

        if ($existingAdmin) {
            $io->error(sprintf('Admin with email "%s" already exists!', $email));
            return Command::FAILURE;
        }

        $admin = new Admin();
        $admin->setEmail($email);
        $admin->setFullName($fullName);
        $admin->setRoles(['ROLE_ADMIN']);
        
        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($admin, $password);
        $admin->setPassword($hashedPassword);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success(sprintf('Admin user "%s" created successfully!', $email));
        $io->note('You can now login at /admin/login');

        return Command::SUCCESS;
    }
}
