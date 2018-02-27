<?php
/**
 * Core file.
 *
 * @author Vince Wooll <sales@jomres.net>
 *
 * @version Jomres 9.9.19
 *
 * @copyright	2005-2018 Vince Wooll
 * Jomres (tm) PHP, CSS & Javascript files are released under both MIT and GPL2 licenses. This means that you can choose the license that best suits your project, and use it accordingly
 **/

// ################################################################
defined('_JOMRES_INITCHECK') or die('');
// ################################################################

class task_admin__test_hook
{
    public function __construct()
    {
        $MiniComponents = jomres_singleton_abstract::getInstance('mcHandler');
        if ($MiniComponents->template_touch) {
            $this->template_touchable = false;

            return;
        }
        if (!translation_user_check()) {
            return;
        }
        echo '<h2>'.jr_gettext('HOOKS_TEST_TEXT', 'HOOKS_TEST_TEXT', false).'</h2><br/>';
    }

    // This must be included in every Event/Mini-component
    public function getRetVals()
    {
        return null;
    }
}
