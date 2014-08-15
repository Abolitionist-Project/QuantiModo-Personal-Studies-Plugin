<div id="section-configure-settings" style="display: none;">

	<div class="col-md-3 col-sm-3">
		<div class="settingsNav">
			<ul>
				<li>
					<button id="button-properties">Properties</button>
					</li>
				<li>
					<button id="button-optimization">Optimization</button>
				</li>
				<li>
					<button id="button-joins">Joins</button>
				</li>
				<li>
					<button id="button-sharing">Sharing</button>
				</li>
			</ul>

			<ul class="buttonRow">
				<li>
					<button id="button-save">Save</button>
					</li>
				<li>
					<button id="button-cancel">Cancel</button>
				</li>
			</ul>
		</div>
	</div>

	<div class="col-md-9 col-sm-9">
		<div id="propertiesContent" class="settings-container open">
			<header class="card-header">
				<h3 class="heading">
					<span>Variable Properties</span>
				</h3>
			</header>
			<div class="settingsContent">
				<table border="0" cellspacing="0">
					<tr>
				    	<td>Variable name</td>
						<td>
							<input id="input-variable-name" type="text" placeholder="">
						</td>
					</tr>
					<tr>
						<td>Unit</td>
						<td>
							<select id="selectVariableUnitSetting"></select>
						</td>
					</tr>
					<tr>
						<td>Category</td>
						<td>
							<select disabled id="selectVariableCategorySetting">
							</select>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div id="optimizationContent" class="settings-container">
			<header class="card-header">
				<h3 class="heading">
					<span>Data Optimization</span>
				</h3>
			</header>
			<div class="settingsContent">
				<table border="0" style="border-collapse:collapse;" cellspacing="0">
				    <tr>
				    	<td>Minimum value</td>
						<td><input type="text" id="variableMinimumValueSetting" placeholder=""><label id="unitForMinValue" class="unitlabel"></label></td>
					</tr>
					<tr>
						<td>Maximum value</td>
						<td><input type="text" id="variableMaximumValueSetting" placeholder=""><label id="unitForMaxValue" class="unitlabel"></label></td>
					</tr>
					<tr>
						<td>Delay Before <br/>Onset of Action</td>
						<td><input type="text" id="variableOnsetDelayValueSetting" placeholder=""><label id="unitForOnsetDelay" class="unitlabel">hrs</label></td>
					</tr>
					<tr>
						<td>Duration of Action</td>
						<td><input type="text" id="variableDurationOfActionValueSetting" placeholder=""><label id="unitForDurationAction" class="unitlabel">hrs</label></td>
					</tr>
				</table>

				<div>
					When there's no data:
					<div class="indented">
						<input type="radio" name="missingAssumptionGroup" id="assumeMissing" checked="true">
						<label for="assumeMissing">Assume data is missing</label>
					</div>
					<div class="indented">
						<input type="radio" name="missingAssumptionGroup" id="assumeValue">
						<label for="assumeValue">Assume <input id="variableFillingValueSetting" style="text-align: center; width: 50px; height: 26px;" type="text" id="inputVariableMaximumValueSetting" placeholder=""> for that time</label>
					</div>
				</div>
			</div>
		</div>

		
		<div id="joinsContent" class="settings-container">
			<header class="card-header">
				<h3 class="heading">
					<span>Joined Variables</span>
				</h3>
			</header>
			<div class="settingsContent">
				<ul id="joinedVariablesList">
				</ul>
				<select id="joinedVariablePicker"></select>
				<button id="addJoinedVariableButton"></button>
			</div>
		</div>

		<div id="sharingContent" class="settings-container">
			<header class="card-header">
				<h3 class="heading">
					<span>Sharing</span>
				</h3>
			</header>
			<div class="settingsContent">
				Members:
				<div class="indented">
					<input type="radio" name="membersSharing" id="notShared" checked="true">
					<label for="notShared">Not shared</label>
				</div>
				<div class="indented">
					<input type="radio" disabled="disabled" name="membersSharing" id="sharedWithFriends" checked="true">
					<label for="sharedWithFriends" disabled="disabled">With friends only</label>
				</div>
				<div class="indented">
					<input type="radio" name="membersSharing" id="sharedWithMembers" checked="true">
					<label for="sharedWithMembers">With all members</label>
				</div>

				Groups:
				<div class="indented">
					<p style="font-weight: 600;">Not shared with any group</p>
				</div>
			</div>
		</div>
	</div>
</div>  