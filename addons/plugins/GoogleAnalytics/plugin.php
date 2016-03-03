<?php
// Copyright 2014 Toby Zerner
// This file is part of esoTalk. Please see the included license file for usage information.

if (!defined("IN_ESOTALK")) exit;

ET::$pluginInfo["GoogleAnalytics"] = array(
	"name" => "Google Analytics",
	"description" => "Adds a Google Analytics tracking script to every page.",
	"version" => ESOTALK_VERSION,
	"author" => "esoTalk Team",
	"authorEmail" => "support@esotalk.org",
	"authorURL" => "http://esotalk.org",
	"license" => "GPLv2",
	"dependencies" => array(
		"esoTalk" => "1.0.0g4"
	)
);


class ETPlugin_GoogleAnalytics extends ETPlugin {

	public function init()
	{
		ET::define("message.trackingIdHelp", "Get your Tracking ID by going into the <em>Administration</em> section for your Google Analytics Property and selecting <em>Property Settings</em>.");
	}

	/**
	 * Add the Google Analytics tracking code to the <head> of every page.
	 *
	 * @return void
	 */
	public function handler_init($sender)
	{
		if ($trackingId = C("GoogleAnalytics.trackingId")) {
			$sender->addToHead("<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '$trackingId', 'auto');
  ga('send', 'pageview');
</script>");
		}
	}

	// Construct and process the settings form.
	public function settings($sender)
	{
		// Set up the settings form.
		$form = ETFactory::make("form");
		$form->action = URL("admin/plugins/settings/GoogleAnalytics");
		$form->setValue("trackingId", C("GoogleAnalytics.trackingId"));

		// If the form was submitted...
		if ($form->validPostBack()) {

			// Construct an array of config options to write.
			$config = array();
			$config["GoogleAnalytics.trackingId"] = $form->getValue("trackingId");

			// Write the config file.
			ET::writeConfig($config);

			$sender->message(T("message.changesSaved"), "success autoDismiss");
			$sender->redirect(URL("admin/plugins"));

		}

		$sender->data("googleAnalyticsSettingsForm", $form);
		return $this->view("settings");
	}

}
