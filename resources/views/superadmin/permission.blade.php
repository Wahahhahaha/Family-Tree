@extends('layouts.app')

@section('title', 'Permission')
@section('body-class', 'page-family-tree')

@section('content')
<div class="wrapper">
    <section class="management-card">
        <div class="management-head">
            <div>
                <h2>Permission Settings</h2>
                <p>Centang menu yang boleh diakses oleh setiap role.</p>
            </div>
        </div>

        <?php if (session('success')): ?>
            <div class="alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if (session('error')): ?>
            <div class="alert-error"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <?php if ($errors->any()): ?>
            <div class="alert-error">
                <?php foreach ($errors->all() as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/management/permission">
            <?php echo csrf_field(); ?>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Superadmin</th>
                            <th>Admin</th>
                            <th>Family Member</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($permissionMenus ?? []) as $menuKey => $menuLabel): ?>
                            <tr>
                                <td><?php echo e($menuLabel); ?></td>
                                <td>
                                    <input
                                        type="checkbox"
                                        name="permissions[<?php echo e($menuKey); ?>][superadmin]"
                                        value="1"
                                        <?php echo e(!empty($permissionSettings['superadmin'][$menuKey]) ? 'checked' : ''); ?>
                                    >
                                </td>
                                <td>
                                    <input
                                        type="checkbox"
                                        name="permissions[<?php echo e($menuKey); ?>][admin]"
                                        value="1"
                                        <?php echo e(!empty($permissionSettings['admin'][$menuKey]) ? 'checked' : ''); ?>
                                    >
                                </td>
                                <td>
                                    <input
                                        type="checkbox"
                                        name="permissions[<?php echo e($menuKey); ?>][family_member]"
                                        value="1"
                                        <?php echo e(!empty($permissionSettings['family_member'][$menuKey]) ? 'checked' : ''); ?>
                                    >
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="settings-actions">
                <button type="submit" class="btn btn-primary">Save Permission</button>
            </div>
        </form>
    </section>
</div>
@endsection
