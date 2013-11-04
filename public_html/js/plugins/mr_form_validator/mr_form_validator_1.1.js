/*
 * VERSION 1.1
 * "selector" attribute for "elements" in form defintion can be any valid jquery selector
 * Not necessarily an ID

// sample form definition (JSON)

var sampleForm = {
        selector: '#talent-signup-form',
        elements: [
            {
                selector: '#talent-name',   // an ID
                type: 'text',
                required: true,
                minlength: 2,
                maxlength: 65
            },
            {
                selector: '.username',  // a CLASS
                type: 'text',
                required: true,
                minlength: 5,
                maxlength: 45
            },
            {
                selector: 'input[type="password"]', // a valid jQuery selector
                type: 'password',
                required: true,
                minlength: 6,
                maxlength: 20,
                pattern: /^[a-z]{4}[0-9]{2}$/,
                patternMessage: "Required format is 4 letters followed by 2 digits"
            },
            {
                selector: '#retype-pwd',
                type: 'password',
                required: true,
                minlength: 6,
                maxlength: 20
            }
        ]    
    };

*/
$.fn.validateForm = function(jsonFormDef) {
    
    try {
        
        // reset old error messages
        $(this).find('span.error.msg').remove();
        
        for(i=0; i<jsonFormDef.elements.length; i++) {

            curElement = jsonFormDef.elements[i];

            $ipElement = $(jsonFormDef.selector).find(curElement.selector);
            if(typeof $ipElement === 'undefined')
                continue;

            ipValue = $ipElement.val();

            try {
                if(curElement.required !== undefined) {
                    if(ipValue.length === 0)
                        throw 'Cannot be empty!';
                }

                pattern = null;
                patternMessage = 'Invalid value!';
                
                if(curElement.type !== undefined) {
                    switch(curElement.type) {
                        case 'email': 
                            pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
                            patternMessage = 'Not a valid e-mail address!';
                            break;
                        case 'tel': 
                            pattern = /^\d{8,12}$/;
                            patternMessage = 'Not a valid phone number!';
                            break;
                        case 'number': 
                            pattern = /^\d+$/;
                            patternMessage = 'Only numbers allowed!';
                            break;
                    }
                }
                
                // check for user-defined pattern
                if(curElement.pattern !== undefined) {
                    pattern = curElement.pattern;
                    
                    // check for user-defined pattern message
                    if(curElement.patternMessage !== undefined)
                        patternMessage = curElement.patternMessage;
                }
                
                if(pattern !== null) {
                    if(pattern.test(ipValue) === false)
                        throw patternMessage;
                }
                
                if(curElement.minlength !== undefined) {
                    if(ipValue.length < curElement.minlength)
                        throw 'Minimum '+ curElement.minlength +' characters!';
                }
                
                if(curElement.maxlength !== undefined) {
                    if(ipValue.length > curElement.maxlength)
                        throw 'Maximum '+ curElement.maxlength +' characters!';
                }
                
            } catch (e) {
                displayFormValidatorError($ipElement, e);
                throw 'Form validation error!';
            }       
        }
        
    } catch (e) {
        console.log(e);
        return false;
    }
    
};

function displayFormValidatorError($ipElement, message) {
    $('<span class="error-msg">'+ message +'</span>').insertAfter($ipElement);
}