<?php

declare(strict_types=1);

namespace WebServCo\Mail\Contract\Factory;

use PHPMailer\PHPMailer\PHPMailer;

interface PHPMailerFactoryInterface
{
    public function createPHPMailer(): PHPMailer;
}
