<?php
if (!defined('APPLICATION')) {
    exit();
}
/*	Copyright 2015 GyD
*	This program is free software: you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation, either version 3 of the License, or
*	(at your option) any later version.
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details.
*
*	You should have received a copy of the GNU General Public License
*	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

$PluginInfo['Libraries'] = array(
  'Description' => 'Vanilla Libraries',
  'Version' => '0.0.1',
  'RequiredApplications' => null,
  'RequiredTheme' => false,
  'RequiredPlugins' => false,
  'HasLocale' => false,
  'Author' => "GyD",
  'AuthorEmail' => 'github@gyd.be',
  'AuthorUrl' => 'http://github.com/GyD',
  'Hidden' => false
);

/**
 * Libraries Manager for Vanilla
 *
 * Class LibrariesPlugin
 */
class LibrariesPlugin extends Gdn_Plugin
{
    /**
     * Library list
     * @var array
     */
    protected static $libraries = array();

    /**
     * @param DiscussionController|Mixed $Sender
     * @param $libraryName
     * @param bool $minified
     */
    public static function AttachLibrary(&$Sender, $libraryName, $minified = true)
    {
        $library = self::getLibrary($libraryName);

        $folder = self::_getPluginFolder(GetValue('plugin', $library));

        if ($js = self::getJS($libraryName, $minified)) {
            $Sender->AddJsFile($folder . '/' . $js);
        }

        if ($css = self::getCSS($libraryName, $minified)) {
            $Sender->AddCssFile($folder . '/' . $css);
        }

        return;

    }

    /**
     * Get tje plugin folder
     * @param $Plugin
     * @return mixed
     */
    private static function _getPluginFolder($Plugin)
    {
        $Folder = GetValue('PluginRoot', Gdn::PluginManager()->GetPluginInfo($Plugin, Gdn_PluginManager::ACCESS_CLASSNAME));
        $Folder = str_replace(rtrim(PATH_PLUGINS, '/'), 'plugins', $Folder);

        return $Folder;
    }

    /**
     * Get the JS file path
     *
     * @param $libraryName
     * @param $minified
     * @return mixed|null
     */
    private static function getJS($libraryName, $minified)
    {
        return self::getFile($libraryName, $minified, 'js');
    }

    /**
     * @param $libraryName
     * @param $minified
     * @param $fileType
     * @return mixed|null
     */
    private static function getFile($libraryName, $minified, $fileType)
    {

        $library = self::getLibrary($libraryName);

        $file = null;
        if ($minified) {
            $file = GetValueR('files.' . $fileType . '-min', $library);
        }
        if (!$file) {
            $file = GetValueR('files.' . $fileType, $library);
        }

        return $file;
    }

    /**
     * Get the library by name
     *
     * @param $libraryName
     * @return array
     */
    private function getLibrary($libraryName)
    {
        $library = array();

        $libraryName = strtolower($libraryName);

        if (array_key_exists($libraryName, self::$libraries)) {
            $library = self::$libraries[$libraryName];
        }

        return $library;
    }

    /**
     * @param $libraryName
     * @param $minified
     * @return mixed|null
     */
    private static function getCSS($libraryName, $minified)
    {
        return self::getFile($libraryName, $minified, 'css');
    }

    /**
     * Call LibrariesPlugin_Startup Event in order to generate the library list
     *
     * @throws Exception
     */
    public function Gdn_Dispatcher_AppStartup_Handler()
    {
        $this->FireEvent('Startup');
    }

    /**
     * Add multiples libraries to the library list
     *
     * @param $librariesSettings
     * @return bool
     */
    public function addLibraries(array $librariesSettings)
    {
        foreach ($librariesSettings as $libraryName => $librarySettings) {
            $this->addLibrary($libraryName, $librarySettings);
        }
    }

    /**
     * Add a library to the custom library list
     *
     * @param $libraryName
     * @param $librarySettings
     */
    public function addLibrary($libraryName, array $librarySettings)
    {
        $libraryName = strtolower($libraryName);

        if (!array_key_exists('version', $librarySettings)) {
            return;
        }

        if (!array_key_exists($libraryName, self::$libraries) OR $this->isNewer($libraryName, $librarySettings)) {
            self::$libraries[$libraryName] = $librarySettings;
        }

    }

    /**
     * Is the library newer than the stored one ?
     *
     * @param $libraryName
     * @param $librarySettings
     * @return mixed
     */
    private function isNewer($libraryName, array $librarySettings)
    {
        return version_compare($librarySettings['version'], self::$libraries[$libraryName]['version'], '>');
    }
}