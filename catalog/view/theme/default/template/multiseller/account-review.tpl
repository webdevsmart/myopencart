<?php echo $header; ?>
<div class="container">
    <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
    </ul>
    <?php if (isset($success) && $success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?></div>
    <?php } ?>

    <div class="alert alert-danger" id="error-holder" style="display: none;"></div>

    <div class="row"><?php echo $column_left; ?>
        <?php if ($column_left && $column_right) { ?>
        <?php $class = 'col-sm-6'; ?>
        <?php } elseif ($column_left || $column_right) { ?>
        <?php $class = 'col-sm-9'; ?>
        <?php } else { ?>
        <?php $class = 'col-sm-12'; ?>
        <?php } ?>
        <div id="content" class="<?php echo $class; ?> ms-account-dashboard"><?php echo $content_top; ?>
            <div class="mm_dashboard">
                <h1><i class="fa fa-cog"></i><?php echo $ms_account_review_manage; ?></h1>

                <div id="tab-category">
                    <div class="table-responsive">
                        <table class="mm_dashboard_table table table-borderless table-hover" id="list-reviews">
                            <thead>
                            <tr>
                                <td class="mm_size_medium"><?php echo $ms_account_review_column_product; ?></td>
                                <td class="mm_size_small"><?php echo $ms_account_review_column_rating; ?></td>
                                <td class="mm_size_large"><?php echo $ms_account_review_column_comment; ?></td>
                                <td class="mm_size_tiny"><?php echo $ms_account_review_column_date_added; ?></td>
                                <td class="mm_size_small"><?php echo $ms_status; ?></td>
                                <td class="mm_size_small"><?php echo $ms_action; ?></td>
                            </tr>

                            <tr class="filter">
                                <td><input type="text"/></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php echo $content_bottom; ?>
        </div>
        <?php echo $column_right; ?>
    </div>
</div>
<?php echo $footer; ?>