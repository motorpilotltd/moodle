// Javascript functions for aruponepage course format.
/* eslint-disable camelcase */

M.course = M.course || {};

M.course.format = M.course.format || {};

/**
 * Get sections config for this format
 * @return {object} section list configuration
 */
M.course.format.get_config = function() {
    return {
        container_node: 'div',
        container_class: 'formataruponepagecontent',
        section_node: 'li',
        section_class: 'section'
    };
};
