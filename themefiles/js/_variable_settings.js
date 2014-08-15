var VariableSettings = function() 
{
	// The variable currently "open" in settings
	var current = null;

	/*
	**	Initialize onclicks, options, etc
	*/
	var init = function(options)
	{
		if (options) 
		{
			jQuery.extend(VariableSettings.params, options);
		}

		// Setup menu buttons
		jQuery(document).on('click', '#button-properties', onMenuButtonClicked);
		jQuery(document).on('click', '#button-optimization', onMenuButtonClicked);
		jQuery(document).on('click', '#button-joins', onMenuButtonClicked);
		jQuery(document).on('click', '#button-sharing', onMenuButtonClicked);

		// Activate cancel/save buttons on varsettings
		jQuery(document).on('click', '#button-save', onSaveButtonClicked);
		jQuery(document).on('click', '#button-cancel', onCancelButtonClicked);

		// Plus button next to the joined variable selector
		jQuery("#addJoinedVariableButton").click(onAddJoinedVariableButtonClicked);
		// Remove button next to joined variables
		jQuery('#joinedVariablesList').on('click', 'li > *', onRemoveJoinedVariableButtonClicked);        
	};

	/*
	**	Shows variable settings for a given variable
	*/
	var showSettings = function(variable)
	{
		// We can't show settings without a variable
	    if (variable == null)
	    {
	        alert("No variable selected");
	        return;
	    }

	    // Set the current variable, we work with this variable from now on
		VariableSettings.current = variable;

		// Set the default values for the selected variable
		setDefaultVariableSettings();

		// Same for the sharing permissions, which have to be loaded from the server
		getDefaultVariablePermissions();

		// Create the fancybox dialog
		var settingsBox = jQuery('#section-configure-settings');
		jQuery.fancybox(settingsBox, {
			closeBtn: true,
			helpers: {
				overlay: {
					closeClick : false
				}
			},
			keys : {
				close  : null
			}
		});
	};

	var setDefaultVariableSettings = function()
	{
		// Reset the joined variables picker
		jQuery("#addJoinedVariableButton").removeClass("active");
		jQuery("#joinedVariablePicker").removeClass("active");

		jQuery('#variableOnsetDelayValueSetting').val(Math.round(VariableSettings.current.onsetDelay / 3600));
		jQuery('#variableDurationOfActionValueSetting').val(Math.round(VariableSettings.current.durationOfAction / 3600));

		// Fill unit selector
		var selectVariableUnitSetting = jQuery('#selectVariableUnitSetting');
		selectVariableUnitSetting.empty();
		var categories = Object.keys(AnalyzePage.quantimodoUnits);
		var currentCategory;
		var currentUnit;
		var foundUnit = false;
		var count = categories.length, innerCount;
		for(var i = 0; i < count; i++)
		{
			currentCategory = AnalyzePage.quantimodoUnits[categories[i]];
			innerCount = currentCategory.length;
			for(var n = 0; n < innerCount; n++)
			{
				currentUnit = currentCategory[n];
				if (foundUnit)	// If foundUnit = true we're in the right category, so start adding values
				{
					selectVariableUnitSetting.append(jQuery('<option/>').attr('value', currentUnit.abbreviatedName).text(currentUnit.name));
				}
				else if (currentUnit.abbreviatedName == VariableSettings.current.unit)
				{
					foundUnit = true;
					n = -1;	// Reset the position in this category and continue, values will be added next loop;
				}
			}
			if (foundUnit)
			{
				break;
			}
		}

		// Fill variable selector, 
		// TODO: Suggestions at the top?
		jQuery('#joinedVariablePicker').empty();
		var currentVariable;
		var variablesCount = AnalyzePage.quantimodoVariables[VariableSettings.current.category].length;
		if ('joinedVariables' in VariableSettings.current)
		{
			var joinedVariablesCount = VariableSettings.current['joinedVariables'].length;
		}
		else
		{
			var joinedVariablesCount = 0;
		}
		var foundVariable = false;
		for(var n = 0; n < variablesCount; n++)
		{
			currentVariable = AnalyzePage.quantimodoVariables[VariableSettings.current.category][n];
			if (currentVariable.id == VariableSettings.current.id)	// If this is the current variable skip it
			{
				continue;
			}
			var isJoinedVariable = false;
			for(var i = 0; i < joinedVariablesCount; i++)
			{
				if (currentVariable.id == VariableSettings.current['joinedVariables'][i].id)
				{
					isJoinedVariable = true;
				}
			}
			if (!isJoinedVariable)	// If this variable is joined with the one currently being edited, skip it
			{
				jQuery('#joinedVariablePicker').append(jQuery('<option/>').attr('value', currentVariable.originalName).text(currentVariable.name));
			}
		}

		// Display all currently joined variable
		var joinedVariablesList = jQuery('#joinedVariablesList');
		joinedVariablesList.empty();
		for(var i = 0; i < joinedVariablesCount; i++)
		{
			currentVariable = VariableSettings.current['joinedVariables'][i];
			joinedVariablesList.append('<li value="' + currentVariable.originalName + '">' + currentVariable.name + '<div></div></li>');
		}

		// Set current values
		jQuery("#input-variable-name").val(VariableSettings.current.name);
	    jQuery("#input-variable-id").val(VariableSettings.current.id);
		jQuery("#selectVariableUnitSetting").val(VariableSettings.current.unit);
	    jQuery("#selectVariableCategorySetting").val(VariableSettings.current.category);

		jQuery('#unitForMinValue').text(VariableSettings.current.unit);
		if (VariableSettings.current.minimumValue == -Infinity)
		{
			jQuery("#variableMinimumValueSetting").val("-Infinity");
		}
		else
		{
			jQuery("#variableMinimumValueSetting").val(VariableSettings.current.minimumValue);
		}
		
		jQuery('#unitForMaxValue').text(VariableSettings.current.unit);
		if (VariableSettings.current.maximumValue == Infinity)
		{
			jQuery("#variableMaximumValueSetting").val("Infinity");
		}
		else
		{
			jQuery("#variableMaximumValueSetting").val(VariableSettings.current.maximumValue);
		}

        //reset filling value before setting anything
        jQuery("#variableFillingValueSetting").val('');
		if (VariableSettings.current.fillingValue != null)
		{
			jQuery("#variableFillingValueSetting").val(VariableSettings.current.fillingValue);
			jQuery("#assumeValue").prop("checked", true)
		}
		else
		{
			jQuery("#assumeMissing").prop("checked", true)
		}
	};

	var getDefaultVariablePermissions = function()
	{
		Quantimodo.getVariablePermissions(VariableSettings.current.originalName, function(permissions) {
			VariableSettings.current.permissions = permissions;

			setDefaultVariablePermissions();
		});
	};

	var setDefaultVariablePermissions = function()
	{
		// Set to "Not shared" by default
		jQuery('input:radio[id="notShared"]').attr('checked', 'checked');

		if(VariableSettings.current.permissions != null)
		{
			for(var i = 0; i < VariableSettings.current.permissions.length; i++)
			{
				var currentSharingPermission = VariableSettings.current.permissions[i];

				if(currentSharingPermission.target == 0 || currentSharingPermission.target.toLowerCase() == "public")
				{
					// Public sharing here, override default
					jQuery('input:radio[id="sharedWithMembers"]').attr('checked', 'checked');
				}
				else
				{
					// Sharing with either a group or a user, which isnt implemented yet
				}
			}			
		}
		else
		{
			alert("Sharing settings couldn't be loaded");
		}
	};

	var hideSettings = function(saveSettings)
	{
		if(saveSettings)
		{
			var settingsPosted = true;
			var permissionsPosted = true;

			var modifiedVariableSettings = getModifiedVariableSettings();
			if (modifiedVariableSettings.length > 0)	// If there are modified settings
			{
				settingsPosted = false;
				saveVariableSettings(modifiedVariableSettings, function() 
					{
						settingsPosted = true;
						if(permissionsPosted)
						{
							VariableSettings.current = null;
							jQuery.fancybox.close();
							VariableSettings.params.saveCallback();
						}
					});
				return;
			}

			var modifiedVariablePermissions = getModifiedVariablePermissions();
			if (modifiedVariablePermissions.modified.length > 0 || modifiedVariablePermissions.deleted.length)
			{
				permissionsPosted = false;
				saveVariablePermissions(modifiedVariablePermissions, function() 
					{
						permissionsPosted = true;
						if(settingsPosted)
						{
							VariableSettings.current = null;
							jQuery.fancybox.close();
							VariableSettings.params.saveCallback();
						}
					});
				return;
			}

			if(settingsPosted && permissionsPosted)
			{
				VariableSettings.current = null;
				jQuery.fancybox.close();
			}
		}
		else
		{
			VariableSettings.current = null;
			jQuery.fancybox.close();
		}
	};

	var getModifiedVariableSettings = function()
	{
		allNewSettings = [];

		// Create the settings for the selected variable
		var newSettings = {"variable":VariableSettings.current.originalName};

		// If the variable name changed we add it to the settings object
		var newName = jQuery("#input-variable-name").val();
		if (newName != null && newName.length > 0 && newName != VariableSettings.current.name)
		{
			if (newName == VariableSettings.current.originalName)
			{
				newSettings['name'] = null;	// NULL = no modified variable name
			}
			else
			{
				newSettings['name'] = newName;
			}
		}

		// Check if the unit has changed
		var newUnit = jQuery("#selectVariableUnitSetting").val();
		if (newUnit != VariableSettings.current.unit)
		{
			newSettings['unit'] = newUnit;
		}

		// Check if we have a minimum value
		var newMinimumValueStr = jQuery("#variableMinimumValueSetting").val()	// Get it as a string first
		var newMinimumValue = parseFloat(newMinimumValueStr);					// Attempt to parse it to a float
		// If the minimum value is Infinity we convert it to the actual JavaScript infinity
		if (newMinimumValueStr == "-Infinity" || newMinimumValueStr == "Infinity" || newMinimumValueStr == "")  
		{
			newMinimumValue = -Infinity;	
		}
		if (newMinimumValueStr != null && newMinimumValue != VariableSettings.current.minimumValue)
		{
			if (!isNaN(newMinimumValue))
			{
				if (newMinimumValue == Infinity || newMinimumValue == -Infinity)
				{
					newSettings['minimumValue'] = "-Infinity";
				}
				else
				{
					newSettings['minimumValue'] = newMinimumValue;
				}
			}
			else
			{
				alert("Invalid minimum value, must be a number, \"-Infinity\" or \"Infinity\"");
				return;
			}
		}

		// Identical procedure for the maximum value
		var newMaximumValueStr = jQuery("#variableMaximumValueSetting").val()
		var newMaximumValue = parseFloat(newMaximumValueStr);
		if (newMaximumValueStr == "-Infinity" || newMaximumValueStr == "Infinity" || newMaximumValueStr == "") 
		{
			newMaximumValue = Infinity;
		}
		if (newMaximumValueStr != null && newMaximumValue != VariableSettings.current.maximumValue)
		{
			if (!isNaN(newMaximumValue))
			{
				if (newMaximumValue == Infinity || newMaximumValue == -Infinity)
				{
					newSettings['maximumValue'] = "Infinity";
				}
				else
				{
					newSettings['maximumValue'] = newMaximumValue;
				}
			}
			else
			{
				alert("Invalid maximum value, must be a number, \"-Infinity\" or \"Infinity\"");
				return;
			}
		}

		// Check for a modified onset delay
		var newonsetDelayValueStr = jQuery("#variableOnsetDelayValueSetting").val()
		var newonsetDelayValue = parseFloat(newonsetDelayValueStr) * 3600;
		if (newonsetDelayValueStr != null && newonsetDelayValue != VariableSettings.current.onsetDelay)
		{
			if (!isNaN(newonsetDelayValue))
			{
				newSettings['onsetDelay'] = newonsetDelayValue;
			}
			else
			{
				alert("Invalid onset delay value, must be a number. \nCan't be null");
				return;
			}
		}

		// The same for the duration of action
		var newdurationOfActionValueStr = jQuery("#variableDurationOfActionValueSetting").val()
		var newdurationOfActionValue = parseFloat(newdurationOfActionValueStr)  * 3600;
		if (newdurationOfActionValueStr != null && newdurationOfActionValue != VariableSettings.current.durationOfAction)
		{
			if (!isNaN(newdurationOfActionValue))
			{
				newSettings['durationOfAction'] = newdurationOfActionValue;
			}
			else
			{
				alert("Invalid duration of action value, must be a number. \nCan't be null");
				return;
			}
		}

		// Figure out if the "what do to when values are missing" setting has changed
		var assumeMissingChecked = jQuery("#assumeMissing").prop("checked");
		var assumeValueChecked = jQuery("#assumeValue").prop("checked");
		if (assumeMissingChecked)
		{
			if (VariableSettings.current.fillingValue != null)
			{
				newSettings['fillingValue'] = null;
			}
		}
		else if (assumeValueChecked)
		{
			var newFillingValue = parseFloat(jQuery("#variableFillingValueSetting").val());
			if (newFillingValue != VariableSettings.current.fillingValue)
			{
				if (newFillingValue == null || newFillingValue.length == 0)
				{
					newSettings['fillingValue'] = null;
				}
				else if (!isNaN(newFillingValue))
				{
					newSettings['fillingValue'] = newFillingValue;
				}
				else
				{
					alert("Invalid filling value, must be a number.");
					return;
				}
			}
		}

		// Get the settings for joined variables that were modified
		var allNewSettings = getModifiedJoinedVariableSettings();

		// If attributes of this variable have changed, add the settings object to our array
		if (Object.keys(newSettings).length > 1)
		{
			allNewSettings.push(newSettings);
		}

		// Return all new settings to be saved
		return allNewSettings;
	}

	var getModifiedJoinedVariableSettings = function()
	{
		var modifiedJoinedVariableSettings = []

		// Bunch of arrays to make the code below a bit more intuitive
		var newListedVariables = [];
		var currentJoinedVariables = [];

		// Get all listed variables
		jQuery('#joinedVariablesList > li').each(function ()
		{
			newListedVariables.push(this.getAttribute("value"));
		});
		// If this variable already had some joined variables we have to figure out which ones were modified
		if ('joinedVariables' in VariableSettings.current)
		{
			// Get all previously joined variables
			var currentJoinedVariablesCount = VariableSettings.current['joinedVariables'].length;
			for(var i = 0; i < currentJoinedVariablesCount; i++)
			{
				currentJoinedVariables.push(VariableSettings.current['joinedVariables'][i].originalName);
			}

			// Loop through previously joined variables
			for(var i = 0; i < currentJoinedVariables.length; i++)
			{
				var hasMatch = false;
				// Loop through new listed variables
				for(var n = 0; n < newListedVariables.length; n++)
				{
					if (currentJoinedVariables[i] == newListedVariables[n])	// If we have a match the variable is still joined
					{
						hasMatch = true;
						newListedVariables.splice(n, 1);	// Remove the listed variable from the array, so that we can loop through the remaining ones later
						break;
					}
				}
				if (!hasMatch)	// No match, the variable was joined, but is no longer listed
				{
					modifiedJoinedVariableSettings.push({"variable":currentJoinedVariables[i], "joinWith":null});	// Create a settings object to undo the joining
				}
			}
		}

		// Once we get here only unprocessed joins remain
		for(var i = 0; i < newListedVariables.length; i++)
		{
			modifiedJoinedVariableSettings.push({"variable":newListedVariables[i], "joinWith":VariableSettings.current.originalName});
		}

		return modifiedJoinedVariableSettings;
	};

	/*
	**	Returns an object containing the properties "modified" and "deleted"
	**	Only supports public sharing at this point.
	*/
	var getModifiedVariablePermissions = function()
	{
		var modifiedVariablePermissions = [];
		var deletedVariablePermissions = [];

		var allListedPermissions = [];
		if(jQuery('input:radio[id="sharedWithMembers"]').attr('checked') == 'checked')
		{
			// Add public sharing row
			allListedPermissions.push({
				"target": "public",
				"variable": VariableSettings.current.originalName,
				"timezone": jstz.determine().name()
			});
		}

		// If we already had some permissions we have to check which ones are changed
		if ('permissions' in VariableSettings.current)
		{
			// Compare VariableSettings.current.permissions with allListedPermissions
			for(var i = 0; i < VariableSettings.current.permissions.length; i++)
			{
				// Loop through new listed permissions
				var hasMatch = false;
				for(var n = 0; n < allListedPermissions.length; n++)
				{
					if(VariableSettings.current.permissions[i].target == allListedPermissions[n].target)
					{
						// TODO check changed properties
						hasMatch = true;
						allListedPermissions.splice(n, 1);	// Remove the listed permission from the array, so that we can loop through the remaining ones later
						break;
					}
				}
				if (!hasMatch)	// No match, the permission existed, but was removed
				{
					deletedVariablePermissions.push({
						"variable":VariableSettings.current.originalName,
						"target":VariableSettings.current.permissions[i].target});
				}
			}
		}

		// Only modified permissions remain
		var modifiedVariablePermissions = allListedPermissions

		var returnVals = {
			"modified": modifiedVariablePermissions,
			"deleted": deletedVariablePermissions
		};

		console.log(returnVals);
		return returnVals;
	};

	var saveVariableSettings = function(newSettings, onDoneListener) 
	{

		Quantimodo.postVariableUserSettings(newSettings, function()
		{
			onDoneListener();
		});

	}

	/*
	**	Saves newPermissions associative array containing "modified" and "deleted"
	*/
	var saveVariablePermissions = function(newPermissions, onDoneListener) 
	{
		if(newPermissions.modified.length == 0 && newPermissions.deleted.length == 0)
		{
			onDoneListener();
			return;
		}

		var postDone = true;
		var deleteDone = true;
		if(newPermissions.modified.length > 0)
		{
			postDone = false;
			Quantimodo.postVariablePermissions(newPermissions.modified, function()
			{
				postDone = true;
				if(deleteDone)
				{
					onDoneListener();
				}
			});	
		}

		if(newPermissions.deleted.length > 0)
		{
			deleteDone = false;
			Quantimodo.deleteVariablePermissions(newPermissions.deleted, function()
			{
				deleteDone = true;
				if(postDone)
				{
					onDoneListener();
				}
			});
		}
	}

	/**********************
	**	LISTENERS BELOW  **
	***********************/
	var onSaveButtonClicked = function()
	{
		VariableSettings.hide(true);
	};

	var onCancelButtonClicked = function()
	{
		VariableSettings.hide(false);
	};

	/*
	**	React to clicks on the menu buttons on the left of the dialog
	*/
	var onMenuButtonClicked = function(event)
	{
		if(event.target.id == "button-properties")
		{
			// Show properties
			var propertiesContent = jQuery("#propertiesContent");
			if(!propertiesContent.hasClass("open"))
			{
				propertiesContent.addClass("open");
			}
			
			// Hide others
			jQuery("#optimizationContent").removeClass("open");
			jQuery("#joinsContent").removeClass("open");
			jQuery("#sharingContent").removeClass("open");
		}
		else if(event.target.id == "button-optimization")
		{
			// Show optimization
			var propertiesContent = jQuery("#optimizationContent");
			if(!propertiesContent.hasClass("open"))
			{
				propertiesContent.addClass("open");
			}
			
			// Hide others
			jQuery("#propertiesContent").removeClass("open");
			jQuery("#joinsContent").removeClass("open");
			jQuery("#sharingContent").removeClass("open");
		}
		else if(event.target.id == "button-joins")
		{
			// Show joins
			var propertiesContent = jQuery("#joinsContent");
			if(!propertiesContent.hasClass("open"))
			{
				propertiesContent.addClass("open");
			}
			
			// Hide others
			jQuery("#propertiesContent").removeClass("open");
			jQuery("#optimizationContent").removeClass("open");
			jQuery("#sharingContent").removeClass("open");
		}
		else if(event.target.id == "button-sharing")
		{
			// Show sharing
			var propertiesContent = jQuery("#sharingContent");
			if(!propertiesContent.hasClass("open"))
			{
				propertiesContent.addClass("open");
			}
			
			// Hide others
			jQuery("#propertiesContent").removeClass("open");
			jQuery("#optimizationContent").removeClass("open");
			jQuery("#joinsContent").removeClass("open");
		}
	};

	var onAddJoinedVariableButtonClicked = function(event)
	{
		var button = jQuery("#addJoinedVariableButton");
		var picker = jQuery("#joinedVariablePicker");
		if (button.hasClass('active'))
		{
			var selectedOptionElement = jQuery("#joinedVariablePicker option:selected")

			var selectedOriginalName = selectedOptionElement.val();
			var selectedName = selectedOptionElement.text();


			jQuery( '#joinedVariablesList' ).append('<li value="' + selectedOriginalName + '">' + selectedName + '<div></div></li>');


			setTimeout(function()
			{
				selectedOptionElement.remove();
			}, 500);
		}
		button.toggleClass('active');
		picker.toggleClass('active');
	};

	var onRemoveJoinedVariableButtonClicked = function(event)
	{
		var selectedVariableElement = jQuery(event.target).parent();
		var selectedOriginalName = selectedVariableElement.attr('value');
		var selectedName = selectedVariableElement.text();

		jQuery('#joinedVariablePicker').append(jQuery('<option/>').attr('value', selectedOriginalName).text(selectedName));

		selectedVariableElement.remove();
	};

	// Return public stuffs
	return{
			params : {
				saveCallback : function() {}
			},

			init : init,

			show : showSettings,

			hide : hideSettings
		}
}();