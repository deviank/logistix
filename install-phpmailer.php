<?php
/**
 * PHPMailer Installation Helper
 * This script helps you install PHPMailer easily
 */

echo "PHPMailer Installation Helper\n";
echo "============================\n\n";

// Check if Composer is available
$composerAvailable = false;
$composerPath = null;

// Check common Composer locations
$possiblePaths = [
    'composer',
    'composer.phar',
    __DIR__ . '/composer.phar'
];

foreach ($possiblePaths as $path) {
    $output = [];
    $return = 0;
    exec("$path --version 2>&1", $output, $return);
    if ($return === 0) {
        $composerAvailable = true;
        $composerPath = $path;
        break;
    }
}

if ($composerAvailable) {
    echo "✓ Composer found: $composerPath\n\n";
    echo "Installing PHPMailer via Composer...\n";
    echo "Running: $composerPath install\n\n";
    
    $output = [];
    $return = 0;
    exec("$composerPath install 2>&1", $output, $return);
    
    foreach ($output as $line) {
        echo $line . "\n";
    }
    
    if ($return === 0) {
        echo "\n✓ PHPMailer installed successfully!\n";
        echo "\nNext steps:\n";
        echo "1. Configure your email settings in config/config.php\n";
        echo "2. See EMAIL-SETUP.md for detailed instructions\n";
    } else {
        echo "\n✗ Installation failed. Please install manually.\n";
        echo "See EMAIL-SETUP.md for manual installation instructions.\n";
    }
} else {
    echo "✗ Composer not found.\n\n";
    echo "You have two options:\n\n";
    echo "Option 1: Install Composer\n";
    echo "  Download from: https://getcomposer.org/download/\n";
    echo "  Then run: composer install\n\n";
    echo "Option 2: Manual Installation\n";
    echo "  1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer/releases\n";
    echo "  2. Extract and copy the 'src' folder to: phpmailer/src/\n";
    echo "  3. The structure should be: phpmailer/src/PHPMailer.php\n\n";
    echo "After installation, configure email in config/config.php\n";
    echo "See EMAIL-SETUP.md for detailed instructions.\n";
}

echo "\n";
?>

