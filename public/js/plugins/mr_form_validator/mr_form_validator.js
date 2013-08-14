/* 
 * Validate forms
 */

/**
 * 
 * @param {String} form_id without #
 * @returns {Object}
 */
function mr_form_validator(form_id) {
    form_validator = new Object();
    
    form_el = '#'+form_id;
    
    if($(form_el).size() !== 1)
        throw 'Invalid form id!';
    else {
        // create error msg element
        span_err_msg = '<span id="err-for-form-'+ form_id +'" '
            +' class="mrfv-err" style="display:none">'
                +'<span class="message"></span>'
                +'<span class="close">x</span>'
            +'</span>';
        $(form_el).append(span_err_msg);
        form_validator.jq_sel_err_msg = '#err-for-form-'+form_id;
        
        // close
        $(form_validator.jq_sel_err_msg).find('.close').click(function(){
            $(this).parent('span.mrfv-err').hide();
        });
    }
    
    form_validator.extract_attributes = extract_attributes;    
    form_validator.validate_text = validate_text;
    form_validator.validate_tel = validate_tel;
    form_validator.validate_email = validate_email;
    form_validator.validate_checkboxes = validate_checkboxes;
    form_validator.display_err_msg = display_err_msg;
    
    return form_validator;
}

/**
 * 
 * @param {String} jq_selector
 * @returns {Object}
 */
function extract_attributes(jq_selector) {
    dom_obj = new Object();
    
    // required
    dom_obj.is_required = ($(jq_selector).attr('required')===undefined) ? false : true;
    
    // minimum length
    dom_obj.min_length = 1;
    if(typeof($(jq_selector).attr('data-minlength'))!=='undefined')
        dom_obj.min_length = $(jq_selector).attr('data-minlength');
    if(!/^\d+$/.test(dom_obj.min_length))
        dom_obj.min_length = 1;
    
    // maximum length
    dom_obj.max_length = (typeof($(jq_selector).attr('maxlength'))==='undefined') ? null : $(jq_selector).attr('maxlength');
    if(!/^\d+$/.test(dom_obj.max_length))
        dom_obj.max_length = null;
    
    return dom_obj;
}

/**
 * 
 * @param {String} jq_selector
 * @returns {void}
 */
function validate_text(jq_selector) {
    sel_el = extract_attributes(jq_selector);
    sel_el_value = $(jq_selector).val();
    
    if(sel_el.is_required) {
        if(sel_el_value.length === 0) {
            throw mr_exception('Fill in this field!', jq_selector);
        }
        
        if(sel_el_value.length < sel_el.min_length) {
            throw mr_exception('Minimum '+sel_el.min_length+' characters required!', jq_selector);
        }
        
        // reset styling
        $(jq_selector).removeAttr('style');
    }
}

/**
 * 
 * @param {String} jq_selector
 * @returns {void}
 */
function validate_tel(jq_selector) {
    sel_el = extract_attributes(jq_selector);
    sel_el_value = $(jq_selector).val();
    
    if(sel_el.is_required) {
        
        if(sel_el_value.length === 0) {
            throw mr_exception('Fill in this field!', jq_selector);
        }
        
        if(! /^\d+$/.test(sel_el_value)) {
            throw mr_exception('Only numbers allowed!', jq_selector);
        }
        
        if(sel_el_value.length < sel_el.min_length) {
            throw mr_exception('Minimum '+sel_el.min_length+' characters required!', jq_selector);
        }
        
        // reset styling
        $(jq_selector).removeAttr('style');
    }
}

/**
 * 
 * @param {String} jq_selector
 * @returns {void}
 */
function validate_email(jq_selector) {
    sel_el = extract_attributes(jq_selector);
    sel_el_value = $(jq_selector).val();
    
    if(sel_el.is_required) {
        
        if(sel_el_value.length === 0) {
            throw mr_exception('Fill in this field!', jq_selector);
        }
        
        if(! /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(sel_el_value)) {
            throw mr_exception('Not a valid e-mail address!', jq_selector);
        }
        
        // reset styling
        $(jq_selector).removeAttr('style');
    }
}

/**
 * 
 * @param {String} jq_selector
 * @returns {void}
 */
function validate_checkboxes(jq_selector) {
    if($(jq_selector+':checked').size() === 0) {
        throw mr_exception('Select atleast one option!', jq_selector);
    }
    
    // reset styling
    $(jq_selector).removeAttr('style');
}

/**
 * 
 * @param {String} message
 * @param {String} jq_selector
 * @returns {Object}
 */
function mr_exception(message, jq_selector) {
    exc = new Object();
    exc.message = message;
    exc.jq_selector = jq_selector;
    
    return exc;
}

/**
 * 
 * @param {Object} exc
 * @param {String} jq_sel_err_msg
 * @returns {Boolean}
 */
function display_err_msg(exc, jq_sel_err_msg) {
    
    try {
        // set border
        $(exc.jq_selector).css('border','1px solid red');
        $(exc.jq_selector).select();

        // show err message
        $(jq_sel_err_msg).css('display','inline');
        $(jq_sel_err_msg).find('.message').text(exc.message);

        jq_selector_offset = $(exc.jq_selector).offset();
        jq_selector_height = $(exc.jq_selector).outerHeight();
        
        // adjust position
        $(jq_sel_err_msg).css({
            'left': jq_selector_offset.left+'px',
            'top': (jq_selector_offset.top + jq_selector_height)+'px'
        });
    } catch(e) {
        console.log(e);
    }
    return false;
}