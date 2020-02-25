<?php

namespace app\services\messages;

defined('BASEPATH') or exit('No direct script access allowed');

use app\services\messages\AbstractMessage;

class PhpVersionNotice extends AbstractMessage
{
    protected $alertClass = 'warning';

    public function isVisible()
    {
        return version_compare(PHP_VERSION, '7.2', '<') && get_option('show_php_version_notice') == '1' && is_admin();
    }

    public function getMessage()
    {
        ?>
        <div class="mtop15"></div>
        <h4><strong>Outdated PHP Version Detected!</strong></h4><hr />
        <p>
            The system detected that the version of <b>PHP (<?php echo PHP_VERSION; ?>)</b> your server is using is outdated and no longer supported.
        </p>
        <p>
            As the PHP core developers recently released new and improved versions, it's strongly recommended to <b>upgrade to PHP version newer or equal than 7.2</b> to get the best results, you can consult with your hosting provider or server administrator to help you with this process.
        </p>
        <br />
        <p>
           <b>
                IMPORTANT: The next major version release, the CRM will require at least php 7.2, it's strongly recommended to upgrade your PHP version so you can continue to use new and improved versions of the application.
           </b>
        </p>
        <hr />
        <a href="<?php echo admin_url('misc/dismiss_php_version_notice'); ?>" class="alert-link">Got it! Don't show this message again</a>
        <?php
    }
}
