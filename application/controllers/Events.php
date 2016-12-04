<?php
	
defined('BASEPATH') OR exit('No direct script access allowed');

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class Events extends CI_Controller {
	
	
	private $_eventUrl = 'http://www.wegottickets.com';
	private $_algoliaUrl = 'https://places-dsn.algolia.net/1/places/query';

	
	private function _getEventUrl($page = 1)
	{
		return $this->_eventUrl.'/searchresults/page/'.$page.'/all';
	}
	
	/**
	 * _cleanText
	 *
	 * Cleans the string for the date objecs
	 *
	 * @param (type) none
	 * @return (type) string
	 */
	private function _cleanText()
	{
		$stringsToClean = array(
			"Doors");
		return $stringsToClean;
			
	}
	
	/**
	 * _getNumPages
	 *
	 * Guesses the number of pages available on the main event URL
	 *
	 * @param (type) none
	 * @return (type) string
	 */
	private function _getNumPages()
	{
		$client = new Client();
		$selector_pagination = 'a.pagination_link';
		
		$crawler = $client->request('GET', $this->_getEventUrl());
	
		try {
		    $num = $crawler->filter($selector_pagination)->last();
			return $num->text();
		} catch (Exception $e) {
		    echo $e;
		}
		
	}
	
	
	/**
	 * _getHeading
	 *
	 * Gets the heading from the Event HTML
	 *
	 * @param (type) string
	 * @return (type) string
	 */
	private function _getHeading($html)
	{
		$selector = 'h2';
		
		$crawler = new Crawler($html);
		$count = $crawler->filter($selector)->count();
		
		if($count > 0){
			$content = $crawler->filter($selector)->first();
			return $content->text();		
		}else{
			return '';
		}
	}
	
	
	/**
	 * _getVenue
	 *
	 * Gets the Venue from the Event HTML
	 *
	 * @param (type) string
	 * @return (type) string
	 */
	private function _getVenue($html)
	{
		$selector = '.venue-details > h4';
		
		$crawler = new Crawler($html);
		$count = $crawler->filter($selector)->count();
		
		if($count > 0){
			$content = $crawler->filter($selector)->first();
			return $content->text();		
		}else{
			return '';
		}
	}
	
	
	
	/**
	 * _getAddress
	 *
	 * Guess the address from Algolia service
	 *
	 * @param (type) venue(string)
	 * @return (type) object
	 */
	private function _getAddress($venue)
	{
		
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $this->_algoliaUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, '{"query": "'.stripslashes($venue).'"}');
		
		//execute post
		$result = curl_exec($ch);
		
		$result = json_decode($result);

		if(isset($result->hits[0])):
			$addressObject = new stdClass();
			$addressObject->name = $result->hits[0]->locale_names->default;
			$addressObject->countryName = $result->hits[0]->country->default;
			$addressObject->countryCode = $result->hits[0]->country_code;
			$addressObject->coords = $result->hits[0]->_geoloc;
		endif;
		//close connection
		curl_close($ch);

		return $addressObject;
				die();
	}
	
	
	
	/**
	 * _getDate
	 *
	 * Gets the Date from the Event HTML
	 *
	 * @param (type) string
	 * @return (type) string
	 */
	private function _getDate($html)
	{
		$selector = '.venue-details > h4';
		
		$crawler = new Crawler($html);
		$count = $crawler->filter($selector)->count();
		
		if($count > 0){
			$content = $crawler->filter($selector)->eq(1);
			$text = $content->text();
			
			/* Clean unnecesary words */
			foreach ($this->_cleanText() as $stringToClean):
				if (strpos($text, $stringToClean) !== false) {
				    $text = substr($text, 0, strpos($text, "Doors"));
				}
			endforeach;
			
			return $text;		
		}else{
			return '';
		}
	}
	
	
	
	/**
	 * _getPrice
	 *
	 * Gets the Price from the Event HTML
	 *
	 * @param (type) string
	 * @return (type) string
	 */
	private function _getPrice($html)
	{
		$selector = '.searchResultsPrice > strong';
		
		$crawler = new Crawler($html);
		$count = $crawler->filter($selector)->count();
		
		if($count > 0){
			$content = $crawler->filter($selector)->first();
			return $content->text();		
		}else{
			return '';
		}
	}
	
	
	
	/**
	 * _getEventsPage
	 *
	 * Gets all events listed in one page
	 *
	 * @param (type) page(integer), places(boolean)
	 * @return (type) array
	 */
	private function _getEventsPage($page = 1, $places = 0)
	{
		$client = new Client();
		$url = $this->_getEventUrl($page);
		$crawler = $client->request('GET', $url);

	
		$selector_event = '.chatterbox-margin';
		$events_count = $crawler->filter($selector_event)->count();
		
		$eventsObjects = array();
		if($events_count > 0){
			$crawler->filter($selector_event)->each(function ($node,$i) use (&$eventsObjects,$places) {
				$html = $node->html();
				$eventObject = new stdClass();	
				
				$eventObject->artist = $this->_getHeading($html);
				$eventObject->venue = $this->_getVenue($html);
				if($places == 1): $eventObject->address = $this->_getAddress($eventObject->venue); endif;
				$eventObject->date = $this->_getDate($html);
				$eventObject->final_price = $this->_getPrice($html);
				
				if($eventObject->artist!=''){
					$eventsObjects[] = $eventObject;	
				}

			});
		}
		
		return $eventsObjects;
	
	}
	
	

	public function getAllEvents()
	{
		
		$this->load->helper('url');
		$page =  $this->uri->segment(3);
		$places = $this->input->get('places',false);
		$format = $this->input->get('format','json');

		
		if($page == 'all'){
			$page = $this->_getNumPages();
		}elseif($page == ''){
			$page = 1;
		}
		
		$events  = new stdClass();
		$events->data = array();
		$events->page = $page;
		$events->totalPages = $this->_getNumPages();
		
		
		for ($i = 1; $i <= $page; $i++):
			$events->data  = array_merge($events->data , $this->_getEventsPage($i,$places));
		endfor;
		
		$data = array(
		        'events' => $events->data
		);
		
		if($format=='json'):
			$this->load->view('jsonOutput', $data);
		else:
			$this->load->view('debugOutput', $data);
		endif;
	}
}
