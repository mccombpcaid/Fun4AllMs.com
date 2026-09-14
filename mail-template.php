<?php
// mail-template.php

function getBrandedTemplate($title, $content) {
    $year = date('Y');
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            .container { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 24px; overflow: hidden; background-color: #ffffff; }
            .header { background-color: #1e3a8a; padding: 40px 20px; text-align: center; }
            .header h1 { color: #ffffff; margin: 0; text-transform: uppercase; letter-spacing: 2px; font-size: 24px; }
            .content { padding: 40px; color: #374151; line-height: 1.6; font-size: 16px; }
            .footer { background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb; }
            .footer p { margin: 0; font-size: 12px; color: #9ca3af; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
            .btn { display: inline-block; background-color: #2563eb; color: #ffffff !important; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: bold; margin-top: 20px; }
        </style>
    </head>
    <body style='background-color: #f3f4f6; padding: 20px;'>
        <div class='container'>
            <div class='header'>
                <h1>Fun 4 All MS</h1>
            </div>
            <div class='content'>
                <h2 style='color: #111827; margin-top: 0;'>$title</h2>
                $content
            </div>
            <div class='footer'>
                <p>&copy; $year Fun 4 All MS • Mississippi's Favorite Inflatables</p>
            </div>
        </div>
    </body>
    </html>
    ";
}