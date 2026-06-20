<?php

namespace App\Enums;

enum AboutUsContentType: string
{
    case TITLE = 'title';                    // تیتر بزرگ
    case INTRO_TEXT = 'intro_text';          // توضیحات معرفی بالای صفحه
    case DESCRIPTION_TOP = 'description_top'; // توضیح بالای بنر
    case DESCRIPTION_BOTTOM = 'description_bottom';   // توضیحات پایین بنر
    case BANNER_IMAGE = 'banner_image';      // بنر
}