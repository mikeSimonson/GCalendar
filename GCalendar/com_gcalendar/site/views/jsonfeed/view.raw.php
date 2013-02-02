<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Allon Moritz
 * @copyright 2007-2011 Allon Moritz
 * @since 2.2.0
 */

defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

class GCalendarViewJSONFeed extends JViewLegacy {

	public function display($tpl = null) {
		$tz = new DateTimeZone(GCalendarUtil::getComponentParameter('timezone', 'UTC'));
		$start = JFactory::getDate(JRequest::getInt('start'), $tz);
		JRequest::setVar('start', $start->format('U') - $tz->getOffset($start));
		$end = JFactory::getDate(JRequest::getInt('end'), $tz);
		JRequest::setVar('end', $end->format('U') - $tz->getOffset($end));

		$calendars = $this->get('GoogleCalendarFeeds');
		if(!is_array($calendars)){
			$calendars = array();
		}
		$this->calendars = $calendars;
		parent::display($tpl);
	}
}