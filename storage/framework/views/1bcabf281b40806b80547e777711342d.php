<?php
    $containers = $getChildComponentContainers();

    $addAction = $getAction($getAddActionName());
    $cloneAction = $getAction($getCloneActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $moveDownAction = $getAction($getMoveDownActionName());
    $moveUpAction = $getAction($getMoveUpActionName());
    $reorderAction = $getAction($getReorderActionName());

    $isAddable = $isAddable();
    $isCloneable = $isCloneable();
    $isDeletable = $isDeletable();
    $isReorderableWithButtons = $isReorderableWithButtons();
    $isReorderableWithDragAndDrop = $isReorderableWithDragAndDrop();

    $statePath = $getStatePath();
?>

<?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $getFieldWrapperView()] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['field' => $field]); ?>
    <div
        x-data="{}"
        <?php echo e($attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['fi-fo-simple-repeater grid gap-y-4'])); ?>

    >
        <?php if(count($containers)): ?>
            <ul>
                <?php if (isset($component)) { $__componentOriginal30dbd75eb120a380110a2b340cd88f46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal30dbd75eb120a380110a2b340cd88f46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.grid.index','data' => ['default' => $getGridColumns('default'),'sm' => $getGridColumns('sm'),'md' => $getGridColumns('md'),'lg' => $getGridColumns('lg'),'xl' => $getGridColumns('xl'),'twoXl' => $getGridColumns('2xl'),'wire:end.stop' => 'mountFormComponentAction(\'' . $statePath . '\', \'reorder\', { items: $event.target.sortable.toArray() })','xSortable' => true,'dataSortableAnimationDuration' => $getReorderAnimationDuration(),'class' => 'gap-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['default' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getGridColumns('default')),'sm' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getGridColumns('sm')),'md' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getGridColumns('md')),'lg' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getGridColumns('lg')),'xl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getGridColumns('xl')),'two-xl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getGridColumns('2xl')),'wire:end.stop' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('mountFormComponentAction(\'' . $statePath . '\', \'reorder\', { items: $event.target.sortable.toArray() })'),'x-sortable' => true,'data-sortable-animation-duration' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getReorderAnimationDuration()),'class' => 'gap-4']); ?>
                    <?php $__currentLoopData = $containers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $uuid => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li
                            wire:key="<?php echo e($this->getId()); ?>.<?php echo e($item->getStatePath()); ?>.<?php echo e($field::class); ?>.item"
                            x-sortable-item="<?php echo e($uuid); ?>"
                            class="fi-fo-repeater-item simple flex justify-start gap-x-3"
                        >
                            <div class="flex-1">
                                <?php echo e($item); ?>

                            </div>

                            <?php if($isReorderableWithDragAndDrop || $isReorderableWithButtons || $isCloneable || $isDeletable): ?>
                                <ul class="flex items-center gap-x-1">
                                    <?php if($isReorderableWithDragAndDrop): ?>
                                        <li x-sortable-handle>
                                            <?php echo e($reorderAction); ?>

                                        </li>
                                    <?php endif; ?>

                                    <?php if($isReorderableWithButtons): ?>
                                        <li
                                            class="flex items-center justify-center"
                                        >
                                            <?php echo e($moveUpAction(['item' => $uuid])->disabled($loop->first)); ?>

                                        </li>

                                        <li
                                            class="flex items-center justify-center"
                                        >
                                            <?php echo e($moveDownAction(['item' => $uuid])->disabled($loop->last)); ?>

                                        </li>
                                    <?php endif; ?>

                                    <?php if($isCloneable): ?>
                                        <li>
                                            <?php echo e($cloneAction(['item' => $uuid])); ?>

                                        </li>
                                    <?php endif; ?>

                                    <?php if($isDeletable): ?>
                                        <li>
                                            <?php echo e($deleteAction(['item' => $uuid])); ?>

                                        </li>
                                    <?php endif; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal30dbd75eb120a380110a2b340cd88f46)): ?>
<?php $attributes = $__attributesOriginal30dbd75eb120a380110a2b340cd88f46; ?>
<?php unset($__attributesOriginal30dbd75eb120a380110a2b340cd88f46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal30dbd75eb120a380110a2b340cd88f46)): ?>
<?php $component = $__componentOriginal30dbd75eb120a380110a2b340cd88f46; ?>
<?php unset($__componentOriginal30dbd75eb120a380110a2b340cd88f46); ?>
<?php endif; ?>
            </ul>
        <?php endif; ?>

        <?php if($isAddable && $addAction->isVisible()): ?>
            <div class="flex justify-center">
                <?php echo e($addAction); ?>

            </div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php /**PATH D:\Media Files\Desktop\taobao\TaoBao\vendor\filament\forms\resources\views\components\repeater\simple.blade.php ENDPATH**/ ?>