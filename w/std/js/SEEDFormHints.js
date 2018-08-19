// SEEDForm Textbox Hints
// Seeds of Diversity Canada 2011
// 
// Thank you to Drew Noakes http://drewnoakes.com 2006-2010 for this technique
// Seeds of Diversity added:
//     submit handling
//     password type swapping
//     color swapping in JS instead of by CSS class
//     definition of hintText in its own attribute to simplify non-empty values
//     sfActive allows textbox to contain the hint text as a valid value


// define a custom method on the string class to trim leading and training spaces
String.prototype.trim = function() { return this.replace(/^\s+|\s+$/g, ''); };

function SEEDFormHintsInit() {
    var inputs = document.getElementsByTagName('input');
    for( i = 0; i < inputs.length; i++ ) {
        var input = inputs[i];
        if( input.type != "text" && input.type != "password" )  continue;

        // existence of attribute sfHintText triggers the behaviour.  sfHintText replaces value when control is empty.
        var s = input.getAttribute('sfHintText');
        if( !s )  continue;
        input.sfHintText = s;
        
        // passwords will show 'text' with hints and 'password' when active
        input.sfSaveType = input.type;
        
        // This is a text or password INPUT control with sfHintText attribute
        // Make it active if it has a value, inactive (with hint) if the value is empty
        if( !input.value ) {
            sfSetInactive( input );
        } else {
            sfSetActive( input );
        }
        input.onfocus = SEEDFormHintsOnFocus;
        input.onblur = SeedFormHintsOnBlur;
    }
}

function SEEDFormHintsOnFocus() {
    var input = this;
    if( !input.sfActive ) {
        input.value = "";
        sfSetActive( input );
    }
}

function SeedFormHintsOnBlur() {
    var input = this;
    if( input.value.trim().length == 0 ) {
        sfSetInactive( input );
    }
}

function sfSetActive( input )
{
    input.sfActive = true;
    input.style['color'] = "#000";
    input.type = input.sfSaveType;  // restore password types
}

function sfSetInactive( input )
{
    input.sfActive = false;
    input.value = input.sfHintText;
    input.style['color'] = "#888";
    input.type = 'text';  // password controls show hints as plain text
}

function SEEDFormHintsSubmit() {
    var inputs = document.getElementsByTagName('input');
    for( i = 0; i < inputs.length; i++ ) {
        var input = inputs[i];
        if( input.type != "text" && input.type != "password" )  continue;
        if( !input.sfHintText )  continue;

        // remove hint text from inactive controls so it isn't submitted
        if( !input.sfActive ) {
            input.value = "";
        }
    }    
    submit();
}

window.onload = SEEDFormHintsInit;
