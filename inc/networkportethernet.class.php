<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2012 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Damien Touraine
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// NetworkPortEthernet class : Ethernet instantiation of NetworkPort
/// @since 0.84
class NetworkPortEthernet extends NetworkPortInstantiation {


   static function getTypeName($nb=0) {
     return __('Ethernet');
   }


   function getNetworkCardInterestingFields() {
      return array('link.`specificity`' => 'mac');
   }


   function showInstantiationForm(NetworkPort $netport, $options=array(), $recursiveItems) {

      if (!$options['several']) {
         echo "<tr class='tab_bg_1'>";
         $this->showNetpointField($netport, $options, $recursiveItems);
         $this->showNetworkCardField($netport, $options, $recursiveItems);
         echo "</tr>\n";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Ethernet port type') . "</td><td>\n";
      Dropdown::showFromArray('type', array(''   => Dropdown::EMPTY_VALUE,
                                            'T'  => __('Twisted pair (RJ-45)'),
                                            'SX' => __('Multimode fiber'),
                                            'LX' => __('Single mode fiber')),
                              array('value' => $this->fields['type']));
      echo "</td>";
      echo "<td>" . __('Ethernet port speed') . "</td><td>\n";
      Dropdown::showFromArray('speed', array(0     => "",
                                             10    => 10,
                                             100   => 100,
                                             1000  => 1000,
                                             10000 => 10000),
                              array('value' => $this->fields['speed']));
      echo "</td>";
      echo "</tr>\n";

      echo "<tr class='tab_bg_1'>\n";
      $this->showMacField($netport, $options);

      echo "<td>".__('Connected to').'</td><td>';
      self::showConnection($netport, true);
      echo "</td>";
      echo "</tr>\n";
  }


  /**
   * @param $group               HTMLTable_Group object
   * @param $super               HTMLTable_SuperHeader object
   * @param $options    array
  **/
   static function getInstantiationHTMLTable_Headers(HTMLTable_Group $group,
                                                     HTMLTable_SuperHeader $super,
                                                     array $options=array()) {

      $display_options = &$options['display_options'];

      if (isset($_POST['display_ethernet_opposite'])) {
         $display_options['ethernet_opposite'] =($_POST['display_ethernet_opposite'] == 'true');
      } else if (!isset($display_options['ethernet_opposite'])) {
         $display_options['ethernet_opposite'] = false;
      }

      $connect_text = __('Connected to')."<br>";
      if ($display_options['ethernet_opposite']) {
         $connect_text .= "<a href='javascript:reloadTab(\"display_ethernet_opposite=false\");'>" .
                          "<i>" . __('hide the opposite link') . "</i></a>";
      } else {
         $connect_text .= "<a href='javascript:reloadTab(\"display_ethernet_opposite=true\");'>" .
                          "<i>" . __('show the opposite link') . "</i></a>";
      }

      $header = $group->addHeader('Connected', $connect_text, $super);
      DeviceNetworkCard::getHTMLTableHeader('NetworkPortEthernet', $group, $super, $header,
                                            $options);
      $group->addHeader('MAC', __('MAC'), $super, $header);
      $group->addHeader('speed', __('Ethernet port speed'), $super, $header);
      $group->addHeader('type', __('Ethernet port type'), $super, $header);
      NetworkPort_Vlan::getHTMLTableHeader('NetworkPort', $group, $super, $header, $options);
      Netpoint::getHTMLTableHeader('NetworkPortEthernet', $group, $super, $header, $options);
      $group->addHeader('Outlet', __('Network outlet'), $super, $header);

      return $header;

  }


  /**
    * Get HTMLTable row for a given ethernet network port and a given extremity
    *
    * @param $netport         NetworkPort object
    * @param $row             HTMLTable_Row object
    * @param $father          HTMLTable_Cell object : the given extremity
    * @param $options   array of possible options:
    *       - 'dont_display' : array of the elements that must not be display
    *       - 'withtemplate' : integer withtemplate param
    *
    * @return the father cell for the Internet Informations ...
   **/
   private function getEthernetInstantiationHTMLTable_(NetworkPort $netport, HTMLTable_Row $row,
                                                       HTMLTable_Cell $father,
                                                       array $options=array()) {

      DeviceNetworkCard::getHTMLTableCellsForItem($row, $this, $father, $options);

      $row->addCell($row->getHeaderByName('Instantiation', 'MAC'),
                    $netport->fields["mac"], $father);

      if (!empty($this->fields['speed'])) {
         $row->addCell($row->getHeaderByName('Instantiation', 'speed'),
                       $this->fields["speed"], $father);
      }

      if (!empty($this->fields['type'])) {
         $row->addCell($row->getHeaderByName('Instantiation', 'type'),
                       $this->fields["type"], $father);
      }

      NetworkPort_Vlan::getHTMLTableCellsForItem($row, $netport, $father, $options);

      Netpoint::getHTMLTableCellsForItem($row, $this, $father, $options);

   }

  /**
   * @see inc/NetworkPortInstantiation::getInstantiationHTMLTable_()
  **/
   function getInstantiationHTMLTable_(NetworkPort $netport, CommonDBTM $item,
                                       HTMLTable_Row $row, array $options=array()) {

      $connect_cell_value = array(array('function'   => array(__CLASS__, 'showConnection'),
                                        'parameters' => array(clone $netport)));

      $oppositePort = new NetworkPort();
      if ($oppositePort->getFromDB($netport->getContact($netport->getID()))) {

         $display_options = $options['display_options'];

         if ($display_options['ethernet_opposite']) {

            $cell = $row->addCell($row->getHeaderByName('Instantiation', 'Connected'),
                                  __('Local network port'));

            $opposite_cell = $row->addCell($row->getHeaderByName('Instantiation', 'Connected'),
                                           $connect_cell_value);

            $oppositeEthernetPort = $oppositePort->getInstantiation();
            if ($oppositeEthernetPort !== false) {
               $oppositeEthernetPort->getEthernetInstantiationHTMLTable_($oppositePort, $row,
                                                                         $opposite_cell, $options);
            }

            if ($display_options['internet']) {
               NetworkName::getHTMLTableCellsForItem($row, $oppositePort, $opposite_cell, $options);
            }

         } else {

            $cell = $row->addCell($row->getHeaderByName('Instantiation', 'Connected'),
                                  $connect_cell_value);

          }

      } else {
         $cell = $row->addCell($row->getHeaderByName('Instantiation', 'Connected'),
                               $connect_cell_value);
      }

      $this->getEthernetInstantiationHTMLTable_($netport, $row, $cell, $options);

      return $cell;

   }


   /**
    * Display a connection of a networking port
    *
    * @param $netport      to be displayed
    * @param $edit         boolean permit to edit ?
   **/
   static function showConnection($netport, $edit= false) {
      $ID      = $netport->fields["id"];
      if (empty($ID)) {
         return false;
      }

      $device1 = $netport->getItem();

      if (!$device1->can($device1->getID(), 'r')) {
         return false;
      }

      $contact = new NetworkPort_NetworkPort();
      $canedit = $device1->can($device1->fields["id"], 'w');

      if ($contact_id = $contact->getOppositeContact($ID)) {
         $oppositePort = new NetworkPort();
         $oppositePort->getFromDB($contact_id);

         $device2 = $oppositePort->getItem();

         if ($device2->can($device2->fields["id"], 'r')) {
            $networklink = $oppositePort->getLink();
            $tooltip     = Html::showToolTip($oppositePort->fields['comment'],
                                             array('display' => false));
            $netlink     = sprintf(__('%1$s %2$s'),
                                   "<span class='b'>".$networklink."</span>\n", $tooltip);
            //TRANS: %1$s and %2$s are links
            echo "&nbsp;". sprintf(__('%1$s on %2$s'), $netlink,
                                   "<span class='b'>".$device2->getLink()."</span>");
            if ($device1->fields["entities_id"] != $device2->fields["entities_id"]) {
               echo "<br>(". Dropdown::getDropdownName("glpi_entities",
                                                       $device2->getEntityID()) .")";
            }

            // 'w' on dev1 + 'r' on dev2 OR 'r' on dev1 + 'w' on dev2
            if ($canedit
                || $device2->can($device2->fields["id"], 'w')) {
               echo " <span class='b'>";

               echo "<a href=\"".$oppositePort->getFormURL()."?disconnect=".
                  "disconnect&amp;id=".$contact->fields['id']."\">". __('Disconnect').
                  "</a>";

               echo "</span>";
            }

         } else {
            if (rtrim($oppositePort->fields["name"]) != "") {
               $netname = $oppositePort->fields["name"];
            } else {
               $netname = __('Without name');
            }
            printf(__('%1$s on %2$s'), "<span class='b'>".$netname."</span>",
                   "<span class='b'>".$device2->getName()."</span>");
            echo "<br>(" .Dropdown::getDropdownName("glpi_entities",
                                                    $device2->getEntityID()) .")";
         }

      } else {
         echo "<div id='not_connected_display$ID'>" . __('Not connected.') . "</div>";
         if ($canedit) {
            if (!$device1->isTemplate()) {
               if ($edit) {
                  self::dropdownConnect($ID, array('name'        => 'NetworkPortConnect_networkports_id_2',
                                                   'entity'      => $device1->fields["entities_id"],
                                                   'entity_sons' => $device1->isRecursive()));
               } else {
                  echo "<a href=\"".$netport->getFormURL()."?id=$ID\">". __('Connect')."</a>";
               }
            } else {
               echo "&nbsp;";
            }
         }
      }
   }


   /**
    * Make a select box for  connected port
    *
    * @param $ID                 ID of the current port to connect
    * @param $options   array of possible options
    *    - name : string / name of the select (default is networkports_id)
    *    - comments : boolean / is the comments displayed near the dropdown (default true)
    *    - entity : integer or array / restrict to a defined entity or array of entities
    *                   (default -1 : no restriction)
    *    - entity_sons : boolean / if entity restrict specified auto select its sons
    *                   only available if entity is a single value not an array (default false)
    *
    * @return nothing (print out an HTML select box)
   **/
   static function dropdownConnect($ID, $options=array()) {
      global $CFG_GLPI;

      $p['name']        = 'networkports_id';
      $p['comments']    = 1;
      $p['entity']      = -1;
      $p['entity_sons'] = false;

     if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      // Manage entity_sons
      if (!($p['entity'] < 0) && $p['entity_sons']) {
         if (is_array($p['entity'])) {
            _e('entity_sons options are not available with array of entity');
         } else {
            $p['entity'] = getSonsOf('glpi_entities', $p['entity']);
         }
      }

      $rand = mt_rand();
      echo "<input type='hidden' name='NetworkPortConnect_networkports_id_1'value='$ID'>";
      echo "<select name='NetworkPortConnect_itemtype' id='itemtype$rand'>";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";

      foreach ($CFG_GLPI["networkport_types"] as $key => $itemtype) {
         if ($item = getItemForItemtype($itemtype)) {
            echo "<option value='".$itemtype."'>".$item->getTypeName(1)."</option>";
         } else {
            unset($CFG_GLPI["networkport_types"][$key]);
         }
      }
      echo "</select>";

      $params = array('itemtype'        => '__VALUE__',
                      'entity_restrict' => $p['entity'],
                      'networkports_id' => $ID,
                      'comments'        => $p['comments'],
                      'myname'          => $p['name']);

      Ajax::updateItemOnSelectEvent("itemtype$rand", "show_".$p['name']."$rand",
                                    $CFG_GLPI["root_doc"].
                                       "/ajax/dropdownConnectEthernetPortDeviceType.php",
                                    $params);

      echo "<span id='show_".$p['name']."$rand'>&nbsp;</span>\n";

      return $rand;
   }


   function getSearchOptions() {

      $tab                      = array();
      $tab['common']            = __('Characteristics');

      $tab[10]['table']         = $this->getTable();
      $tab[10]['field']         = 'mac';
      $tab[10]['name']          = __('MAC');
      $tab[10]['massiveaction'] = false;

      $tab[11]['table']         = $this->getTable();
      $tab[11]['field']         = 'type';
      $tab[11]['name']          = __('Ethernet port type');
      $tab[11]['massiveaction'] = false;

      $tab[12]['table']         = $this->getTable();
      $tab[12]['field']         = 'speed';
      $tab[12]['name']          = __('Ethernet port speed');
      $tab[12]['massiveaction'] = false;

      return $tab;

   }


   static function getSearchOptionsToAddForInstantiation(array &$tab, array $joinparams,
                                                         $itemtype) {

      $tab[22]['table']         = 'glpi_netpoints';
      $tab[22]['field']         = 'name';
      $tab[22]['name']          = __('Network outlet');
      $tab[22]['forcegroupby']  = true;
      $tab[22]['massiveaction'] = false;
      $tab[22]['joinparams']    = array('jointype'   => 'standard',
                                        'beforejoin'
                                         => array('table'      => 'glpi_networkportethernets',
                                                  'joinparams' => $joinparams));

   }

}
?>
