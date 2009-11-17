<?php
/**
 * Mini-component core file
 * @author Vince Wooll <sales@jomres.net>
 * @version Jomres 4
* @package Jomres
* @subpackage mini-components
* @copyright	2005-2009 Vince Wooll

Jomres is distributed as a mix of two licenses (excepting other files in the libraries folder, which are independantly licensed). 

The first, proprietary license, refers to Jomres as a package. You cannot share it, period. You can see the full license here http://www.jomres.net/license.html. There are some exceptions, and these files are independantly licensed (see the /jomres/libraries/phptools folder for example)
The files in the /jomres/core-minicomponents,  /jomres/libraries/jomres/cms_specific and the /jomres/templates folders, whilst copyright Vince Wooll, are licensed differently to allow those users who wish, to develop and distribute their own third party plugins for Jomres. Those files are licensed under the MIT license, which allows third party vendors to modify them to suit their own requirements and if so desired, distribute them for free or cost. 

################################################################
This file is subject to The MIT License. For licencing information, please visit 
http://www.jomres.net/index.php?option=com_content&task=view&id=214&Itemid=86 and http://www.jomres.net/license.html
################################################################
*/

// ################################################################
defined( '_JOMRES_INITCHECK' ) or die( 'Direct Access to this file is not allowed.' );
// ################################################################

/**
#
 * Show the property header
 #
* @package Jomres
#
 */
class j01070showpropertyheader
	{
	/**
	#
	 * Constructor: Constructs and displays header details through the property header patTemplate file. Optionally, if a property header override file has been installed (allows different headers for SRPs and multirooms) then it will show that instead
	#
	 */
	function j01070showpropertyheader($componentArgs)
		{
		// Must be in all minicomponents. Minicomponents with templates that can contain editable text should run $this->template_touch() else just return
		$MiniComponents =jomres_getSingleton('mcHandler');
		if ($MiniComponents->template_touch)
			{
			$this->template_touchable=false; return;
			}
		$mrConfig=getPropertySpecificSettings();
		$siteConfig = jomres_getSingleton('jomres_config_site_singleton');
		$jrConfig=$siteConfig->get();
		$output=array();
		$pageoutput = array();

		$property_uid=(int)$componentArgs['property_uid'];
		

		
		jr_import('jomres_cache');
		$cache = new jomres_cache("property_header",$property_uid,false);
		$cacheContent = $cache->readCache();
		if ($cacheContent)
			echo $cacheContent;
		else
			{
			if (is_null($property_uid))
				$property_uid		= intval(jomresGetParam( $_REQUEST, 'property_uid', 0 ));
			if ($property_uid>0)
				{
				$current_property_details =jomres_getSingleton('basic_property_details');
				$current_property_details->gather_data($property_uid);
				
				$stars=$current_property_details->stars;
				$starslink="<img src=\"".get_showtime('live_site')."/jomres/images/blank.png\" border=\"0\" HEIGHT=\"1\" hspace=\"10\" VSPACE=\"1\" alt=\"blank\" />";
				if ($stars!="0")
					{
					$starslink="";
				  	for ($i=1;$i<=$stars;$i++)
			    		{
						$starslink.="<img src=\"".get_showtime('live_site')."/jomres/images/star.png\" border=\"0\" alt=\"star\" />";
						}
					$starslink.="";
					}
				$output['IMAGE']=getImageForProperty("property",$property_uid,$property_uid);

				$output['MOSCONFIGLIVESITE']=get_showtime('live_site');

				$sizes=array('thwidth'=>$jrConfig['thumbnail_width'],'thheight'=>$jrConfig['thumbnail_width']);
				if (file_exists(JOMRES_IMAGELOCATION_ABSPATH.$property_uid."_property_".$property_uid.".jpg"))
					$sizes=getImagesSize(JOMRES_IMAGELOCATION_ABSPATH.$property_uid."_property_".$property_uid.".jpg");

				$output['TOOLTIP_PROPERTY_IMAGE']=jomres_makeTooltip("property_image","",$output['IMAGE'],$output['IMAGE'],"","imageonly",$type_arguments=array("width"=>$sizes['thwidth'],"height"=>$sizes['thheight'],"border"=>0));
				
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				$rtRows=array();
				$roomtypes=array();
				if ($mrConfig['singleRoomProperty'] != "1")
					{
					$RoomClassAbbvs = array();
					$query = "SELECT room_classes_uid,room_class_abbv,room_class_full_desc,image FROM #__jomres_room_classes";
					$roomsClassList =doSelectSql($query);
					foreach ($roomsClassList as $roomClass)
						{
						$RoomClassAbbvs[(int)$roomClass->room_classes_uid] = array( 
							'abbv'=>
							jr_gettext('_JOMRES_CUSTOMTEXT_ROOMTYPES_ABBV'.(int)$roomClass->room_classes_uid,stripslashes($roomClass->room_class_abbv),false,false),
							'desc'=>
							jr_gettext('_JOMRES_CUSTOMTEXT_ROOMTYPES_DESC'.(int)$roomClass->room_classes_uid,stripslashes($roomClass->room_class_full_desc),false,false),
							'image'=>
							$roomClass->image
							);
						}
					$property['HRTYPES']	=	"";
					$query="SELECT room_classes_uid FROM #__jomres_rooms WHERE propertys_uid = '".(int)$property_uid."' ";
					$rt= doSelectSql($query);
					if (count($rt)>0)
						{
						$roomTypeArray=array();
						foreach ($rt as $roomtype)
							{
							$roomTypeArray[]=$roomtype->room_classes_uid;
							}

						if (count($roomTypeArray)>1)
							$roomTypeArray=array_unique($roomTypeArray);
						if (count($roomTypeArray)>0)
							{
							$property['HRTYPES']=jr_gettext('_JOMRES_FRONT_ROOMTYPES',_JOMRES_FRONT_ROOMTYPES);
							foreach ($roomTypeArray as $type)
								{
								$rtRows=array();
								$roomtype_abbv = $RoomClassAbbvs[$type]['abbv'];
								$roomtype_desc = $RoomClassAbbvs[$type]['desc'];
								$rtRows['ROOM_TYPE']=jomres_makeTooltip($roomtype_abbv,$roomtype_abbv,$roomtype_desc,$RoomClassAbbvs[$type]['image'],"","room_type",array());
								$roomtypes[]=$rtRows;
								}
							}
						}
					}
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				$features=$current_property_details->features;
				if (count($features) > 0)
					{
					foreach ($features as $f)
						{
						$propertyFeatureDescriptionsArray['FEATURE']=jomres_makeTooltip($f['abbv'],$f['abbv'],$f['desc'],$f['image'],"","property_feature",array());
						$featureList[]=$propertyFeatureDescriptionsArray;
						}
					}
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					
				if (strlen($current_property_details->metatitle)>0)
					jomres_cmsspecific_setmetadata("title",stripslashes($current_property_details->metatitle));
				else
					{
					jomres_cmsspecific_setmetadata("title",$current_property_details->get_property_name($property_uid));
					}

				jomres_cmsspecific_setmetadata('description',stripslashes($current_property_details->metadescription));
				jomres_cmsspecific_setmetadata('keywords',$current_property_details->property_town.", ".$current_property_details->property_region.", ".$current_property_details->property_country);

				$output['STARS']=$starslink;
				$output['PROPERTY_NAME'] = $current_property_details->property_name;
				$output['TOWN'] = $current_property_details->property_town;
				$output['REGION'] = $current_property_details->property_region;
				$output['COUNTRY'] = $current_property_details->property_country;

				$pageoutput[]=$output;
				$tmpl = new patTemplate();

				$mcOutput=$MiniComponents->getAllEventPointsData('01070');
				if (count($mcOutput)>0)
					{
					foreach ($mcOutput as $key=>$val)
						{
						$tmpl->addRows( 'customOutput_'.$key, array($val) );
						}
					}
				$componentArgs=array('tmpl'=>$tmpl);
				
				if ($mrConfig['singleRoomProperty'] == "0" && $MiniComponents->eventFileExistsCheck('00220') )
					{
					$templatePath=$MiniComponents->triggerEvent('00220',$componentArgs); //
					}
				elseif ($MiniComponents->eventFileExistsCheck('00222') )
					{
					$templatePath=$MiniComponents->triggerEvent('00222',$componentArgs); //
					}
				else
					{
					$tmpl->setRoot( JOMRES_TEMPLATEPATH_FRONTEND );
					$tmpl->addRows( 'pageoutput',$pageoutput);
					$tmpl->addRows('featurelist',$featureList);
					$tmpl->addRows('roomtypes',$roomtypes);
					$tmpl->readTemplatesFromInput( 'property_header.html');
					$cachableContent = $tmpl->getParsedTemplate();
					$cache->setCache($cachableContent);
					unset($cache);
					echo $cachableContent;
					
					}

				if ($jrConfig['dumpTemplate']=="1")
					$tmpl->dump();
				}
			}
		}

	/**
	#
	 * Must be included in every mini-component
	#
	 * Returns any settings the the mini-component wants to send back to the calling script. In addition to being returned to the calling script they are put into an array in the mcHandler object as eg. $mcHandler->miniComponentData[$ePoint][$eName]
	#
	 */
	// This must be included in every Event/Mini-component
	function getRetVals()
		{
		return null;
		}
	}
?>