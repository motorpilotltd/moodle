<?php

use block_my_cohort_cert\my_cohort_cert;

/**
 * my_cohort_cert block renderer
 *
 * @package    block_my_cohort_cert
 */

defined('MOODLE_INTERNAL') || die;

class block_my_cohort_cert_renderer extends plugin_renderer_base {

    /**
     * Show tree with cohorts and certificatoin
     *
     * @param $data
     * @return string
     */
    public function show_tree($data) {
        $navigationattrs = array(
            'class' => 'block_tree list',
            'role' => 'tree');
        $content = $this->tree($data, $navigationattrs);

        return $content;
    }

    /**
     * Build the the basing on the node
     *
     * @param $node
     * @param array $attrs
     * @param int $depth
     * @return string
     */
    public function tree($node, $attrs=array(), $depth = 1){
        $items = $node['children'];

        if (count($items)==0) {
            return '';
        }

        $lis = array();
        $number = 0;
        foreach ($items as $item) {
            $number++;

            $isbranch = count($item['children']) > 0;
            if ($isbranch) {
                $item['hideicon'] = false;
            }

            $content = $item['content'];
            $id = html_writer::random_id();
            $ulattr = ['id' => $id . '_group', 'role' => 'group'];
            $liattr = ['class' => ['depth_'.$depth], 'tabindex' => '-1'];
            $pattr = ['class' => ['tree_item'], 'role' => 'treeitem'];
            if ($isbranch) {
                $liattr['class'][] = 'contains_branch';
                $pattr += ['aria-expanded' => 'false'];
                $pattr += ['aria-owns' => $id . '_group'];
            }

            $nodetextid = 'label_' . $depth . '_' . $number;

            $pattr['class'][] = 'tree_item';
            if ($isbranch) {
                $pattr['class'][] = 'branch';
            } else {
                $pattr['class'][] = 'leaf';
            }

            $liattr['class'] = join(' ', $liattr['class']);
            $pattr['class'] = join(' ', $pattr['class']);

            if (isset($pattr['aria-expanded']) && $pattr['aria-expanded'] === 'false') {
                $ulattr += ['aria-hidden' => 'true'];
            }

            $content = html_writer::tag('p', $content, $pattr) . $this->tree($item, $ulattr, $depth + 1);
            $liattr['aria-labelledby'] = $nodetextid;
            $content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }

        if (count($lis)) {
            if (empty($attrs['role'])) {
                $attrs['role'] = 'group';
            }
            return html_writer::tag('ul', implode("\n", $lis), $attrs);
        } else {
            return '';
        }
    }
}
