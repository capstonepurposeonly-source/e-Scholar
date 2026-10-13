<?php
/**
 * Quick script to create an admin user
 * Usage: php create_admin.php
 */

require __DIR__.'/vendor/autoload.php';

use App\Entity\Admin;
use Doctrine\ORM\EntityManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Bootstrap Symfony
$kernel = new \App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

// Get services
$entityManager = $container->get('doctrine')->getManager();
$passwordHasher = $container->get(UserPasswordHasherInterface::class);

// Admin credentials
$email = 'admin@norsu.edu.ph';
$password = 'admin123';  // Change this after first login!
$fullName = 'NORSU Administrator';

// Check if admin already exists
$adminRepo = $entityManager->getRepository(Admin::class);
$existingAdmin = $adminRepo->findOneBy(['email' => $email]);

if ($existingAdmin) {
    echo "❌ Admin user already exists with email: {$email}\n";
    echo "✓ Email: {$existingAdmin->getEmail()}\n";
    echo "✓ Name: {$existingAdmin->getFullName()}\n";
    echo "\nIf you forgot the password, delete the admin and run this script again:\n";
    echo "DELETE FROM admin WHERE email = '{$email}';\n";
    exit(1);
}

// Create new admin
$admin = new Admin();
$admin->setEmail($email);
$admin->setFullName($fullName);
$admin->setRoles(['ROLE_ADMIN']);

// Hash password
$hashedPassword = $passwordHasher->hashPassword($admin, $password);
$admin->setPassword($hashedPassword);

// Save to database
$entityManager->persist($admin);
$entityManager->flush();

echo "\n✅ Admin user created successfully!\n\n";
echo "═══════════════════════════════════════\n";
echo "   Admin Login Credentials\n";
echo "═══════════════════════════════════════\n";
echo "Email:    {$email}\n";
echo "Password: {$password}\n";
echo "Name:     {$fullName}\n";
echo "═══════════════════════════════════════\n\n";
echo "⚠️  IMPORTANT: Change this password after first login!\n";
echo "📱 Login URL: http://localhost:8000/admin/login\n\n";
