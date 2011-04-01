<?php
namespace Swiftriver\Core\Modules\SiSPS\Parsers;
/**
 * @author thomas.smyth[at]gatech[dot]edu
 * @author schultzd[at]mit[dot]edu
 */
class UshahidiParser implements IParser {
    /**
     * This method returns a string array with the names of all
     * the source types this parser is designed to parse. For example
     * the RSSParser may return array("Blogs", "News Feeds");
     *
     * @return string[]
     */
    public function ListSubTypes() {
        return array(
            "All Reports"
        );
    }

    /**
     * This method returns a string describing the type of sources
     * it can parse. For example, the RSSParser returns "Feeds".
     *
     * @return string type of sources parsed
     */
    public function ReturnType() {
        return "Ushahidi";
    }

    /**
     * This method returns an array of the required paramters that
     * are nessesary to run this parser. The Array should be in the
     * following format:
     * array(
     *  "SubType" => array ( ConfigurationElements )
     * )
     *
     * @return array()
     */
    public function ReturnRequiredParameters(){
        return array(
            "Search" => array (
                new \Swiftriver\Core\ObjectModel\ConfigurationElement(
                        "Instance URL",
                        "string",
                        "The root URL of the Ushahidi instance"
                )
            )
        );
    }

    /**
     * Given a set of parameters, this method should
     * fetch content from a channel and parse each
     * content into the Swiftriver object model :
     * Content Item. The $lastSuccess datetime is passed
     * to the function to ensure that content that has
     * already been parsed is not duplicated.
     *
     * @param \Swiftriver\Core\ObjectModel\Channel $channel
     * @return Swiftriver\Core\ObjectModel\Content[] contentItems
     */
    public function GetAndParse($channel) {
      return array();
    }
}