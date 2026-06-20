<?php

namespace App\Enums;

enum AboutUsContentType: string
{
    case Title = 'title';                    // تیتر بزرگ
    case IntroText = 'intro_text';          // توضیحات معرفی بالای صفحه
    case DescriptionTop = 'description_top'; // توضیح بالای بنر
    case DescriptionBottom = 'description_bottom';   // توضیحات پایین بنر
    case BannerImage = 'banner_image';      // بنر
}