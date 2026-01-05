<?php

namespace App\Http\Requests;

use App\Models\Video;
use Illuminate\Foundation\Http\FormRequest;

class VideoRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'title' => 'required|string|max:255',
      'original_link' => 'required|string|max:255',
      'poster' => 'required|string|max:1000',
      'type' => 'required|string|max:255|in:' . implode(',', Video::TYPES),
      'ad_vast' => 'nullable|string|max:1000',
      'enable_button_download' => 'required|boolean',
      'download_link' => 'nullable|string|max:1000',
      'subtitles' => 'nullable|array',
      'subtitles.*.language' => 'required|string|size:2',
      'subtitles.*.name' => 'required|string|max:255',
      'subtitles.*.file' => 'required|file|mimes:vtt,srt|max:2048',
    ];

    if ($this->isMethod('post')) {
      return [
        'title' => 'required|string|max:255',
        'original_link' => 'required|string|max:255',
        'poster' => 'required|string|max:1000',
        'type' => 'required|string|max:255|in:' . implode(',', Video::TYPES),
        'ad_vast' => 'nullable|string|max:1000',
        'enable_button_download' => 'required|boolean',
        'download_link' => 'nullable|string|max:1000',
        'subtitles' => 'nullable|array',
        'subtitles.*.language' => 'required|string|size:2',
        'subtitles.*.name' => 'required|string|max:255',
        'subtitles.*.file' => 'required|file|mimes:vtt,srt|max:2048',
      ];
    }

    if ($this->isMethod('put')) {
      return [
        'title' => 'required|string|max:255',
        'original_link' => 'required|string|max:255',
        'poster' => 'required|string|max:1000',
        'type' => 'required|string|max:255|in:' . implode(',', Video::TYPES),
        'ad_vast' => 'nullable|string|max:1000',
        'enable_button_download' => 'required|boolean',
        'download_link' => 'nullable|string|max:1000',
        'subtitles' => 'nullable|array',
        'subtitles.*.language' => 'required|string|size:2',
        'subtitles.*.name' => 'required|string|max:255',
        'subtitles.*.file' => 'required|file|mimes:vtt,srt|max:2048',
      ];
    }
  }

  public function messages(): array
  {
    return [
      'title.required' => 'The title field is required.',
      'original_link.required' => 'The original link field is required.',
      'poster.required' => 'The poster field is required.',
      'type.required' => 'The type field is required.',
      'ad_vast.required' => 'The ad vast field is required.',
      'enable_button_download.required' => 'The enable button download field is required.',
      'download_link.required' => 'The download link field is required.',
      'subtitles.required' => 'The subtitles field is required.',
      'subtitles.*.language.required' => 'The subtitles language field is required.',
      'subtitles.*.name.required' => 'The subtitles name field is required.',
      'subtitles.*.file.required' => 'The subtitles file field is required.',
    ];
  }
}
