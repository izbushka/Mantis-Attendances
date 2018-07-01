<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin();
print_manage_menu();

?>
    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>
        <div class="form-container" >

            <form action="<?php echo plugin_page( 'config_edit' ) ?>" method="post">
                <?php echo form_security_field( 'plugin_Attendances_config_edit' ) ?>
                <div class="widget-box widget-color-blue2">
                    <div class="widget-header widget-header-small">
                        <h4 class="widget-title lighter">
                            <i class="ace-icon fa fa-text-width"></i>
                            <?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' ) ?>
                        </h4>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main no-padding">
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed table-striped">
                                    <tr <?php echo helper_alternate_class() ?>>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'lbl_report_project_id' ) ?>
                                        </td>
                                        <td class="center" colspan="2">
                                            <label><input type="text" name="report_project_id" class="input-sm" pattern="[0-9]+"
                                                          value="<?php echo(plugin_config_get( 'report_project_id' )) ?>"/></label>
                                        </td>
                                    </tr>
                                    <tr <?php echo helper_alternate_class() ?>>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'lbl_report_category_id' ) ?>
                                        </td>
                                        <td class="center" colspan="2">
                                            <label><input type="text" name="report_category_id" class="input-sm" pattern="[0-9]+"
                                                          value="<?php echo(plugin_config_get( 'report_category_id' )) ?>"/></label>
                                        </td>
                                    </tr>
                                    <tr <?php echo helper_alternate_class() ?>>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'lbl_default_summary' ) ?>
                                        </td>
                                        <td class="center" colspan="2">
                                            <label><input type="text" name="default_summary" class="input-sm"
                                                          value="<?php echo(plugin_config_get( 'default_summary' )) ?>"/></label>
                                        </td>
                                    </tr>
                                    <tr <?php echo helper_alternate_class() ?>>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'lbl_default_description' ) ?>
                                        </td>
                                        <td class="center" colspan="2">
                                            <label><input type="text" name="default_description" class="input-sm"
                                                          value="<?php echo(plugin_config_get( 'default_description' )) ?>"/></label>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="widget-toolbox padding-8 clearfix">
                            <input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'change_configuration' )?>" />
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
layout_page_end();
