<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;

use Event;
use Illuminate\Support\Facades\Validator;
use App\Api\v1\Controllers\DanteBaseController;
use App\Api\Rules\StartOrEndDateRule;
use App\Api\v1\Models\GetModel;
use App\Api\v1\Controllers\InsertController;

/**
 * @brief Used to insert seismic data into DB.
 *
 * Questa classe viene utilizzata per inserire 'event', 'hypocenter', 'magnitude', 'pick', 'amplitude', 'phase', ecc... nel DB.
 * L'idea alla base e' che ogni entita' che voglia inserire un "oggetto" di quelli riportati sopra nel DB, debba passare per questa classe.
 * 
 * Ad esempio, la classe 'InsertEwController' creta per ricevere i messaggi da EW, alla crea un oggetto di questa classe per effettuare l'inserimento nel DB.
 * 
 * Lo Swagger di esempio per inserire un evento si trova sotto la rotta '/event/1/' qui: [http://webservices.ingv.it/swagger-ui/dist/?url=http%3A%2F%2Fjabba.int.ingv.it%3A10013%2Fingvws%2Feventdb%2F1%2Fswagger_full.json](http://webservices.ingv.it/swagger-ui/dist/?url=http%3A%2F%2Fjabba.int.ingv.it%3A10013%2Fingvws%2Feventdb%2F1%2Fswagger_full.json)
 *
 */
class GetController extends DanteBaseController
{
    
    /*
     * 
     */
    public function getEventsPref(Request $request)
    {
        \Log::debug("START - ".__CLASS__.' -> '.__FUNCTION__);
        
        $input_parameters = $request->only([
			'starttime', 'endtime',
			'minlat', 'maxlat', 'minlon', 'maxlon',
			'lat', 'lon', 'minradius', 'maxradius',
			'minradiuskm', 'maxradiuskm',
			'minmag', 'maxmag', 
            'mindepth', 'maxdepth',
            'orderby', 
            'page'
        ]);
        \Log::debug(' getting only params: ',$input_parameters);

        /* Validator */
        $validator_default_check    = config('dante.validator_default_check');
        $validator_default_message  = config('dante.validator_default_messages');
        $validator = Validator::make($input_parameters, [
            'starttime'			=> [new StartOrEndDateRule],
            'endtime'			=> [new StartOrEndDateRule],
			'lat'				=> ['bail','numeric','min:-90','max:90', function($attribute, $value, $fail) use ($input_parameters) {
									if (
											(!isset($input_parameters['minradius']) || !isset($input_parameters['maxradius']))
											&&
											(!isset($input_parameters['minradiuskm']) || !isset($input_parameters['maxradiuskm']))
											) {
											return $fail('"'.$attribute.'" require "minradius" and "maxradius" or "minradiuskm" and "maxradiuskm".');
										}
								}],
			'lon'				=> ['bail','numeric','min:-180','max:180', function($attribute, $value, $fail) use ($input_parameters) {
									if (
											(!isset($input_parameters['minradius']) || !isset($input_parameters['maxradius']))
											&&
											(!isset($input_parameters['minradiuskm']) || !isset($input_parameters['maxradiuskm']))
											) {
											return $fail('"'.$attribute.'" require "minradius" and "maxradius" or "minradiuskm" and "maxradiuskm".');
										}
								}],
            'minradius'			=> 'bail|required_with:maxradius|'.$validator_default_check['radius'],
			'maxradius'			=> 'bail|required_with:minradius|'.$validator_default_check['radius'],
            'minradiuskm'		=> 'bail|required_with:maxradiuskm|'.$validator_default_check['radiuskm'],
            'maxradiuskm'		=> 'bail|required_with:minradiuskm|'.$validator_default_check['radiuskm'],          
            'minlat'			=> $validator_default_check['lat'],
            'maxlat'			=> $validator_default_check['lat'],
            'minlon'			=> $validator_default_check['lon'],
            'maxlon'			=> $validator_default_check['lon'],          
            'minmag'			=> $validator_default_check['magnitude'],
            'maxmag'			=> $validator_default_check['magnitude'],
            'mindepth'			=> $validator_default_check['depth'],
            'maxdepth'			=> $validator_default_check['depth'],
            'orderby'			=> $validator_default_check['orderby'].',hyp_ot-asc,hyp_ot-desc,mag_mag-asc,mag_mag-desc',            
            'page'				=> $validator_default_check['page'],
        ], $validator_default_message)->validate();
                                
        /* get data */
        $data = GetModel::getEventsPref($input_parameters);
        
        \Log::debug("END - ".__CLASS__.' -> '.__FUNCTION__);
        return $data;
    }
    
    /*
     * 
     */
    public function getEvents(Request $request)
    {   
        \Log::debug("START - ".__CLASS__.' -> '.__FUNCTION__);
        
        $input_parameters = $request->only([
			'starttime', 'endtime',
			'minlat', 'maxlat', 'minlon', 'maxlon',
			'lat', 'lon', 'minradius', 'maxradius',
			'minradiuskm', 'maxradiuskm',
			'minmag', 'maxmag', 
            'mindepth', 'maxdepth',
            'wheretypehypnamein', 
            'whereinstancein',
			'id_locator',
            'orderby', 
            'page',
			'event_group_id'
        ]);
        \Log::debug(' getting only params: ',$input_parameters);

        /* Validator */
        $validator_default_check    = config('dante.validator_default_check');
        $validator_default_message  = config('dante.validator_default_messages');
        $validator = Validator::make($input_parameters, [
            'starttime'				=> [new StartOrEndDateRule],
            'endtime'				=> [new StartOrEndDateRule],
			'lat'					=> ['bail','numeric','min:-90','max:90', function($attribute, $value, $fail) use ($input_parameters) {
										if (
												(!isset($input_parameters['minradius']) || !isset($input_parameters['maxradius']))
												&&
												(!isset($input_parameters['minradiuskm']) || !isset($input_parameters['maxradiuskm']))
												) {
												return $fail('"'.$attribute.'" require "minradius" and "maxradius" or "minradiuskm" and "maxradiuskm".');
											}
									}],
			'lon'					=> ['bail','numeric','min:-180','max:180', function($attribute, $value, $fail) use ($input_parameters) {
										if (
												(!isset($input_parameters['minradius']) || !isset($input_parameters['maxradius']))
												&&
												(!isset($input_parameters['minradiuskm']) || !isset($input_parameters['maxradiuskm']))
												) {
												return $fail('"'.$attribute.'" require "minradius" and "maxradius" or "minradiuskm" and "maxradiuskm".');
											}
									}],
            'minradius'				=> 'bail|required_with:maxradius|'.$validator_default_check['radius'],
			'maxradius'				=> 'bail|required_with:minradius|'.$validator_default_check['radius'],
            'minradiuskm'			=> 'bail|required_with:maxradiuskm|'.$validator_default_check['radiuskm'],
            'maxradiuskm'			=> 'bail|required_with:minradiuskm|'.$validator_default_check['radiuskm'], 
            'minlat'				=> $validator_default_check['lat'],
            'maxlat'				=> $validator_default_check['lat'],
            'minlon'				=> $validator_default_check['lon'],
            'maxlon'				=> $validator_default_check['lon'],
            'minmag'				=> $validator_default_check['magnitude'],
            'maxmag'				=> $validator_default_check['magnitude'],
            'mindepth'				=> $validator_default_check['depth'],
            'maxdepth'				=> $validator_default_check['depth'],
			'whereinstancein'		=> 'string',
			'wheretypehypnamein'	=> 'string',
			'id_locator'			=> ['bail', 'integer', function($attribute, $value, $fail) {
										// Validate that 'whereinstancein' is passed as GET param
										if(!isset($input_parameters['whereinstancein'])) {
											return $fail('"'.$attribute.'" require "whereinstancein".');
										}
										
										// Validate that exists an 'event' with 'whereinstancein' and 'id_locator'
										$obj = new InsertController();
										$exists = 0;
										foreach (explode(',', $input_parameters['whereinstancein']) as $instance) {
											$getEvent = $obj->getFilteredEvent(['instance' => $instance, 'id_locator' => $input_parameters['id_locator']]);
											if ($getEvent->exists()) {
												$exists = 1;
											}
										}
										if ($exists == 0) {
											return $fail('the couple "'.$attribute.'" and "provenance.instance" do not exist.');
										}
									}],
            'orderby'				=> $validator_default_check['orderby'].',hyp_ot-asc,hyp_ot-desc,mag_mag-asc,mag_mag-desc',            
            'page'					=> $validator_default_check['page'],
            'format'				=> 'in:json',
            'formatted'				=> $validator_default_check['formatted'],
            'limit'					=> $validator_default_check['limit'],
			'event_group_id'		=> $validator_default_check['event__fk_events_group'],
        ], $validator_default_message)->validate();
                                    
        /* get data */
        $data = GetModel::getEvents($input_parameters);
        
        \Log::debug("END - ".__CLASS__.' -> '.__FUNCTION__);
        return $data;
    }
    
    public function getEvent(Request $request)
    {   
        \Log::debug("START - ".__CLASS__.' -> '.__FUNCTION__);
        
        $input_parameters = $request->only([
			'originid',
			'eventid'
        ]);
        \Log::debug(' getting only params: ',$input_parameters);
        
        /* Validator */
        $validator_default_check    = config('dante.validator_default_check');
        $validator_default_message  = config('dante.validator_default_messages');
        $validator = Validator::make($input_parameters, [
            'originid'      => 'integer|exists:hypocenter,id',
			'eventid'       => 'integer|exists:event,id',
            'orderby'       => $validator_default_check['orderby'].',ot-asc,ot-desc,mag-asc,mag-desc',            
            'page'          => $validator_default_check['page'],
            'format'        => $validator_default_check['format'],
            'formatted'     => $validator_default_check['formatted'],
            'limit'         => $validator_default_check['limit'],
        ], $validator_default_message)->validate();
        
        /* get data */
        $data = GetModel::getEvent($input_parameters);
        
        \Log::debug("END - ".__CLASS__.' -> '.__FUNCTION__);
        return $data;
    }
}