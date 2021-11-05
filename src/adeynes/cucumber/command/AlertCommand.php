<?php
declare(strict_types=1);

namespace adeynes\cucumber\command;

use adeynes\cucumber\Cucumber;
use adeynes\cucumber\utils\MessageFactory;
use adeynes\parsecmd\command\blueprint\CommandBlueprint;
use adeynes\parsecmd\command\ParsedCommand;
use pocketmine\command\CommandSender;

use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use CortexPE\DiscordWebhookAPI\Embed;

class AlertCommand extends CucumberCommand
{

    public function __construct(Cucumber $plugin, CommandBlueprint $blueprint)
    {
        parent::__construct(
            $plugin,
            $blueprint,
            'alert',
            'cucumber.command.alert',
            'Broadcast a message to the server',
            '/alert <message> [-nomessage|-nom] [-popup|-p] [-title|-t]'
        );
    }

    public function _execute(CommandSender $sender, ParsedCommand $command): bool
    {
        [$message] = $command->get(['message']);
        $message = MessageFactory::colorize($message);
        $server = $this->getPlugin()->getServer();

        // Can't use ! because empty string evals to false
        if (is_null($command->getFlag('nomessage'))) {
            $server->broadcastMessage($message);
        }

        if (!is_null($command->getFlag('popup'))) {
            $server->broadcastPopup($message);
        }

        if (!is_null($command->getFlag('title'))) {
            $server->broadcastTitle('', $message); // broadcast a subtitle, title is too big
        }

        $this->getPlugin()->formatAndSend($sender, 'success.alert', ['message' => $message]);

        // send details on discord server
        $whook = $this->getPlugin()->getConfig()->get('webh2');
        $webhook = new Webhook($whook);

        $msg = new Message();
        $msg->setUsername("cucumALERTS");
        $msg->setAvatarURL("https://th.bing.com/th/id/R.3e31457af0eba4508a0f69e2aa4415f8?rik=okgaal1d19EDsg&riu=http%3a%2f%2fpngimg.com%2fuploads%2fcucumber%2fcucumber_PNG84281.png&ehk=1SM1m9pziiqKralyNFy2tsj4Hp%2fBWelIZK8Y2BVqG5s%3d&risl=&pid=ImgRaw&r=0");
        $msg->setContent("Announcement from Server");

        $embed = new Embed();
        $embed->setTitle("Announcement");
        $embed->setColor(0x00FFFF);
        $embed->addField("ℹ", $message . " - sent by " . $sender->getName());
        $embed->setFooter("🥒", "https://github.com/Lycol50/cucumber");
        $msg->addEmbed($embed);

        $webhook->send($msg);
        
        return true;
    }

}