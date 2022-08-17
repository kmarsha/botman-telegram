<?php

use Botman\Botman\Botman;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\SymfonyCache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\Drivers\Telegram\TelegramDriver;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Attachments\Video;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Conversations\Conversation;

require "vendor/autoload.php";

$configs = [
  "telegram" => [
    // "token" => "5396273704:AAFJ_Wsf-KOfiqpO2NjHUw3PAJcB2umpIkI"
    // "token" => //your telegram bot token
  ],
];

$adapter = new FilesystemAdapter();
// $adapter = new AdapterInterface();
DriverManager::loadDriver(TelegramDriver::class, new SymfonyCache($adapter));

$botman = BotManFactory::create($configs);

$botman->hears('/start', function (Botman $bot) {
  $bot->reply('Hi!');
  $bot->reply('Welcome ^^');
});

$botman->hears('/commands', function (Botman $bot) {
  $bot->reply("Available Commands: \n
  /start 
  /stop 
  /commands 
  hi 
  nama aku {nama kamu} 
  ex. nama aku macca 
  aku mau beli {angka} buah permen/snack 
  ex. aku mau beli 2 buah permen
        aku mau beli 3 buah snack 
  kirimi aku gambar kucing 
  kirimi aku video kucing");
});

$botman->hears('/stop', function (Botman $bot) {
  $bot->reply('Bye Bye ^^');
})->stopsConversation();

// $botman->hears('Hello', function($bot) {
//   $bot->startConversation(new OnboardingConversation); //ask function is not worked out
// });

// class OnboardingConversation extends Conversation
// {
//     protected $firstname;

//     protected $email;

//     public function askFirstname()
//     {
//         $this->ask('Hello! What is your firstname?', function(Answer $answer) {
//             // Save result
//             $this->firstname = $answer->getText();

//             $this->say('Nice to meet you '.$this->firstname);
//             $this->askEmail();
//         });
//     }

//     public function askEmail()
//     {
//         $this->ask('One more thing - what is your email?', function(Answer $answer) {
//             // Save result
//             $this->email = $answer->getText();

//             $this->say('Great - that is all we need, '.$this->firstname);
//         });
//     }

//     public function run()
//     {
//         // This will be called immediately
//         $this->askFirstname();
//     }
// }

$botman->hears("hi", function (Botman $bot) {
  $bot->reply("Halo ^^");
  $bot->typesAndWaits(2);
  $bot->ask('Anything I can do for you today?', function($answer, $bot){ # ask not get answer
      $bot->say("Oh, really! You said '{$answer->getText()}'... is that right?");
  });
});

$botman->hears("nama aku {nama}", function (Botman $bot, $nama) {
  $bot->reply("salam kenal $nama!");
  $bot->reply("senang berkenalan denganmu ^^");
});

$botman->hears("aku mau beli ([0-9]+) buah (permen|snack)", function (Botman $bot, $qty, $item) {
  // $bot->reply("kamu mau beli $qty buah $item?");
  $bot->ask("kamu mau beli $qty buah $item?", function (Botman $bot, $qty, $item, Answer $response) {
    $respon = $response->getText();
    $bot->reply($respon);
    if ($respon == 'ya') {
      $bot->reply("kamu beli $qty buah $item");
    } elseif ($respon == 'gak') {
      $bot->reply("kamu gak jadi beli $qty buah $item");
    }
  });
});

$botman->hears("kirimi aku gambar kucing", function (Botman $bot) {
  $attachment =  new Image("https://i.pinimg.com/736x/35/f1/52/35f152efa11406e3a124f5b4d9bef680.jpg");
  $message = OutgoingMessage::create("ini gambar kucing nya")
            ->withAttachment($attachment);

  $bot->reply($message);
  $bot->reply("semoga kamu suka gambarnya ^^");
});

$botman->hears("kirimi aku video kucing", function (Botman $bot) {
  $attachment =  new Video("https://v.pinimg.com/videos/mc/720p/0b/ea/e6/0beae6a76c3a2f04b0c08b66d637cdc3.mp4");
  $message = OutgoingMessage::create("ini video kucing nya")
            ->withAttachment($attachment);

  $bot->reply($message);
  $bot->reply("semoga kamu suka videonya ^^");
});

$botman->fallback(function (Botman $bot) {
  $message = $bot->getMessage()->getText();
  $bot->reply("'$message'?");
  $bot->reply("Maksudnya?");
  $bot->reply("Aku gangerti :,((");
  $bot->reply("Cek command yang tersedia yah ^^ /commands");
});

$botman->listen();