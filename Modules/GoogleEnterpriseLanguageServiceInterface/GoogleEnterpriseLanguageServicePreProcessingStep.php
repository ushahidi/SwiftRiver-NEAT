<?php
namespace Swiftriver\PreProcessingSteps;
class GoogleEnterpriseLanguageServicePreProcessingStep implements \Swiftriver\Core\PreProcessing\IPreProcessingStep {

    public function __construct()
    {
        
    }

    /**
     * 
     *
     * @param \Swiftriver\Core\ObjectModel\Content[] $contentItems
     * @param \Swiftriver\Core\Configuration\ConfigurationHandlers\CoreConfigurationHandler $configuration
     * @param \Log $logger
     * @return \Swiftriver\Core\ObjectModel\Content[]
     */
    public function Process($contentItems, $configuration, $logger)
    {
        $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [Method invoked]", \PEAR_LOG_DEBUG);

        //if the content is not valid, jsut return it
        if(!isset($contentItems) || !is_array($contentItems) || count($contentItems) < 1)
        {
            $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [No content supplied]", \PEAR_LOG_DEBUG);
            
            $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [Method finished]", \PEAR_LOG_DEBUG);

            return $contentItems;
        }

        $config = \Swiftriver\Core\Setup::DynamicModuleConfiguration()->Configuration;

        //Check for the existance of config for this pre processing step
        if(!key_exists($this->Name(), $config))
        {
            $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [The Google Enterprise Language Service Turbine was called but no configuration exists for this module]", \PEAR_LOG_ERR);

            $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [Method finished]", \PEAR_LOG_DEBUG);

            return $contentItems;
        }

        //Get the config
        $config = $config[$this->Name()];

        //Check that all the config entries are there
        foreach($this->ReturnRequiredParameters() as $requiredParam)
        {
            if(!key_exists($requiredParam->name, $config))
            {
                $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [The Google Enterprise Language Service Turbine  was called but all the required configuration properties could not be loaded]", \PEAR_LOG_ERR);

                $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [Method finished]", \PEAR_LOG_DEBUG);

                return $contentItems;
            }
        }

        //get the api key
        $apikey = (string) $config["Google API Key"]->value;

        //get the base language for this swift instance
        $baseLanguageCode = $configuration->BaseLanguageCode;

        //create the return array
        $translatedContent = array();

        $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [START: Looping through content items]", \PEAR_LOG_DEBUG);

        //Loop throught the content
        foreach($contentItems as $content)
        {
            $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [START: Running Workflow for content]", \PEAR_LOG_DEBUG);

            //Get the first language specific text blok
            $lsp = \reset($content->text);

            //If the language code is set and matches the base language code then skip it
            if(isset($lsp->languageCode) &&  $lsp->languageCode != null && \strtolower($lsp->languageCode) == \strtolower($baseLanguageCode))
            {
                $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [Content text is already in the base language]", \PEAR_LOG_DEBUG);

                $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [END: Running Workflow for content]", \PEAR_LOG_DEBUG);

                $translatedContent[] = $content;

                continue;
            }

            try 
            {
                //Get the title
                $title = $lsp->title;

                //url encode the title
                $title = \urlencode($title);

                //Get the text
                $text = "";

                //Concatenate all text
                foreach($lsp->text as $t)
                    $text .= " $t";

                //Urlencode text
                $text = \urlencode($text);

                //Construct the service uri
                $uri = "https://www.googleapis.com/language/translate/v2?key=$apikey&target=$baseLanguageCode&q=$title&q=$text";

                //create a service wrapper
                $serviceWrapper = new \Swiftriver\Core\Modules\SiSW\ServiceWrapper($uri);

                //Call the service and get back the json
                $json = $serviceWrapper->MakeGETRequest();

                //decode the json
                $object = \json_decode($json);

                //if not translation was required
                if(\reset($object->data->translations)->detectedSourceLanguage == \strtolower($baseLanguageCode))
                {
                    $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [Google Language service detected that content text is already in the base language]", \PEAR_LOG_DEBUG);

                    $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [END: Running Workflow for content]", \PEAR_LOG_DEBUG);

                    //Set the language code
                    $lsp->languageCode = $baseLanguageCode;

                    //Set the text block back to the content item
                    $content->text[0] = $lsp;
                }
                else
                {
                    $languagecode = \reset($object->data->translations)->detectedSourceLanguage;

                    $title = \reset($object->data->translations)->translatedText;

                    $text = \count($object->data->translations) > 1
                        ? $object->data->translations[1]->translatedText
                        : "";

                    $newLsp = new \Swiftriver\Core\ObjectModel\LanguageSpecificText
                        (
                            $baseLanguageCode,
                            $title,
                            array($text)
                        );

                    $lsp->languageCode = $languagecode;

                    $content->text[0] = $newLsp;

                    $content->text[1] = $lsp;
                }

                $translatedContent[] = $content;
            }
            catch (\Exception $e)
            {

                $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [$e]", \PEAR_LOG_ERR);

                $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [An exception was throw, moving to the next content item]", \PEAR_LOG_DEBUG);
                
                $translatedContent[] = $content;
            }

            $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [END: Running Workflow for content]", \PEAR_LOG_DEBUG);
        }

        $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [END: Looping through content items]", \PEAR_LOG_DEBUG);

        $logger->log("PreProcessingSteps::GoogleEnterpriseLanguageServicePreProcessingStep::Process [Method finished]", \PEAR_LOG_DEBUG);

        //return the translated content
        return $translatedContent;
    }

    public function Description()
    {
        return "This plugin automatically translates your content from any language ".
               "supported by the Google Language Toolkit into the base language".
               "specified during installation.";
    }
    public function Name()
    {
        return "Google Enterprise Language Services";
    }
    
    public function ReturnRequiredParameters()
    {
        return array
        (
            new \Swiftriver\Core\ObjectModel\ConfigurationElement
            (
                "Google API Key",
                "string",
                "The Google Enterprise API Key, if you dont have on, you should be using the none enterprise Google Language Service Turbine"
            )
        );
    }
}
?>
