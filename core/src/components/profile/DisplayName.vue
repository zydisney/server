<!--
  - @copyright Copyright (c) 2021 Julius Härtl <jus@bitgrid.net>
  -
  - @author Julius Härtl <jus@bitgrid.net>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -
  -->

<template>
	<div>
		<form id="displaynameform" class="section">
			<h3>
				<label for="displayname">Full name</label>
				<a href="#" class="federation-menu" aria-label="Change privacy level of full name">
						<span class="icon-federation-menu icon-contacts-dark">
							<span class="icon-triangle-s"></span>
						</span>
					<div class="federationScopeMenu popovermenu bubble menu menu-center"></div></a>
			</h3>
			<input type="text" id="displayname" name="displayname" value="admin" autocomplete="on" autocapitalize="none" autocorrect="off">
			<span class="icon-checkmark hidden"></span>
			<span class="icon-error hidden"></span>
			<input type="hidden" id="displaynamescope" value="v2-federated">
		</form>
	</div>
</template>

<script>
export default {
	name: 'DisplayName'
}
</script>

<style scoped>

</style>
