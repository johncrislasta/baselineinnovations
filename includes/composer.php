<?php

namespace VeutifyApp;


$model = 'model.hello';

$stepper = new Stepper\Stepper();
$stepper->attr('v-model', $model );

$button1 = new Form\Basic('button', 'Continue');
$button1->attrs( [
	'color' => 'primary',
	'@click' => $model.' = 2'
] );

$button2 = new Form\Basic('button', 'Continue');
$button2->attrs( [
	'color' => 'primary',
	'@click' => $model.' = 3'
] );

$button3 = new Form\Basic('button', 'Continue');
$button3->attrs( [
	'color' => 'primary',
	'@click' => $model.' = 1'
] );

$step1 = new Stepper\Step( 'Name of step 1', '<v-card class="mb-12" color="grey lighten-1" height="200px"></v-card>'.$button1);
$step2 = new Stepper\Step( 'Name of step 2', '<v-card class="mb-12" color="grey lighten-1" height="200px"></v-card>'.$button2);
$step3 = new Stepper\Step( 'Name of step 3', '<v-card class="mb-12" color="grey lighten-1" height="200px"></v-card>'.$button3);

$step1->tab->condition($model .' > 1');
$step2->tab->condition($model .' > 2');

$stepper->addStep( $step1 );
$stepper->addStep( $step2 );
$stepper->addStep( $step3 );



$model2 = 'model.hello2';

$stepper2 = new Stepper\Stepper();
$stepper2->attr('v-model', $model2 );

$button12 = new Form\Basic('button', 'Continue');
$button12->attrs( [
	'color' => 'primary',
	'@click' => $model2.' = 2'
] );

$button22 = new Form\Basic('button', 'Continue');
$button22->attrs( [
	'color' => 'primary',
	'@click' => $model2.' = 3'
] );

$button32 = new Form\Basic('button', 'Continue');
$button32->attrs( [
	'color' => 'primary',
	'@click' => $model2.' = 1'
] );

$step12 = new Stepper\Step( 'Name of step 1', $stepper.$button12 );
$step22 = new Stepper\Step( 'Name of step 2', '<v-card class="mb-12" color="grey lighten-1" height="200px"></v-card>'.$button22 );
$step32 = new Stepper\Step( 'Name of step 3', '<v-card class="mb-12" color="grey lighten-1" height="200px"></v-card>'.$button32 );

$step12->tab->condition($model2 .' > 1');
$step22->tab->condition($model2 .' > 2');

$stepper2->addStep( $step12 );
$stepper2->addStep( $step22 );
$stepper2->addStep( $step32 );

$stepper2->setDivider('');

$quoteInstance = new Layout\App( new Layout\Main( new Layout\Container(

	$stepper2

) ) );