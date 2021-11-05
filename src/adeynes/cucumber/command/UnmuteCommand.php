<?php
declare(strict_types=1);

namespace adeynes\cucumber\command;

use adeynes\cucumber\Cucumber;
use adeynes\cucumber\utils\CucumberException;
use adeynes\cucumber\utils\CucumberPlayer;
use adeynes\cucumber\utils\Queries;
use adeynes\parsecmd\command\blueprint\CommandBlueprint;
use adeynes\parsecmd\command\ParsedCommand;
use pocketmine\command\CommandSender;

use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use CortexPE\DiscordWebhookAPI\Embed;


class UnmuteCommand extends CucumberCommand
{

    public function __construct(Cucumber $plugin, CommandBlueprint $blueprint)
    {
        parent::__construct(
            $plugin,
            $blueprint,
            'unmute',
            'cucumber.command.unmute',
            'Unmute a player',
            '/unmute <player>'
        );
    }

    public function _execute(CommandSender $sender, ParsedCommand $command): bool
    {
        [$target_name] = $command->get(['player']);
        $target_name = strtolower($target_name);

        try {
            $this->getPlugin()->getPunishmentRegistry()->removeMute($target_name);
            $this->getPlugin()->getConnector()->executeChange(
                Queries::CUCUMBER_PUNISH_UNMUTE,
                ['player' => $target_name]
            );

            if ($target = CucumberPlayer::getOnlinePlayer($target_name)) {
                $this->getPlugin()->formatAndSend(
                    $target,
                    'moderation.mute.unmute.manual',
                    ['moderator' => $sender->getName()]
                );
            }

            $this->getPlugin()->formatAndSend($sender, 'success.unmute', ['player' => $target_name]);

            // send details on discord server
            $whook = $this->getPlugin()->getConfig()->get('webh');
            $webhook = new Webhook($whook);

            $msg = new Message();
            $msg->setUsername("cucumBAN");
            $msg->setAvatarURL("https://th.bing.com/th/id/R.3e31457af0eba4508a0f69e2aa4415f8?rik=okgaal1d19EDsg&riu=http%3a%2f%2fpngimg.com%2fuploads%2fcucumber%2fcucumber_PNG84281.png&ehk=1SM1m9pziiqKralyNFy2tsj4Hp%2fBWelIZK8Y2BVqG5s%3d&risl=&pid=ImgRaw&r=0");
            $list = array("wowowowowow", "nice", "oh wow!", "heyyyyy");
            $msg->setContent("");

            $embed = new Embed();
            $embed->setTitle("UNMUTE");
            $embed->setColor(0x00FF00);
            $embed->addField(array_rand($list), "> " . $target_name . " has been unmute by " . $sender->getName());
            $embed->setFooter("🥒", "https://github.com/Lycol50/cucumber");
            $msg->addEmbed($embed);

            $webhook->send($msg);
            return true;
        } catch (CucumberException $exception) {
            $sender->sendMessage($exception->getMessage());
            return false;
        }
    }

}
