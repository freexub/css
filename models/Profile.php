<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "profile".
 *
 * @property int $id номер
 * @property string $fname фамилия
 * @property string $name имя
 * @property string $sname отчество
 * @property string $profile_type_id тип профиля: студ., препод., сотрудник
 * @property string $faculty_id ID факультета
 * @property string $edu_form_id ID формы обучения
 * @property string $edu_level_id ID уровня обучения
 * @property string $lang_id ID языка
 * @property string $stage_id ID ступени обучения (студ, маг, док.)
 * @property string $course_num Номер курса
 * @property string $sex_id ID пол
 * @property string $student_id ID студента
 * @property string $user_id ID пользователя
 * @property string $speciality_id ID специальности
 * @property string $iin
 * @property string $email
 * @property string $review
 * @property string $claim
 * @property string $document
 */
class Profile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'profile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fname', 'name', 'sname'], 'required'],
            [['fname', 'name', 'sname'], 'string', 'max' => 150],
            [['profile_type_id', 'faculty_id', 'edu_form_id', 'edu_level_id', 'lang_id', 'stage_id', 'course_num', 'sex_id', 'student_id', 'user_id', 'speciality_id', 'review', 'claim', 'document'], 'string', 'max' => 250],
            [['iin'], 'string', 'max' => 12],
            [['email'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fname' => 'Fname',
            'name' => 'Name',
            'sname' => 'Sname',
            'profile_type_id' => 'Profile Type ID',
            'faculty_id' => 'Faculty ID',
            'edu_form_id' => 'Edu Form ID',
            'edu_level_id' => 'Edu Level ID',
            'lang_id' => 'Lang ID',
            'stage_id' => 'Stage ID',
            'course_num' => 'Course Num',
            'sex_id' => 'Sex ID',
            'student_id' => 'Student ID',
            'user_id' => 'User ID',
            'speciality_id' => 'Speciality ID',
            'iin' => 'Iin',
            'email' => 'Email',
            'review' => 'Review',
            'claim' => 'Claim',
            'document' => 'Document',
        ];
    }
}
