# AI Services

## Gemini Files

- `includes/services/gemini/gemini-api.php`: accepts base64 image plus prompt, optional extra images.
- `includes/services/gemini/gemini-api-text.php`: text-only prompt flow for image generation.
- `includes/services/gemini/gemini-api-multi-image.php`: two-image prompt flow.
- `includes/services/gemini/gemini-api-campaign.php`: campaign image generation wrapper with primary image, extra references, prompt, aspect ratio, and image size.

## Model And Mockup Prompt Sources

- Model-specific prompt builders live in `includes/modules/modelos-ai/modelos-ai-ajax.php`.
- Mockup prompt templates live in `includes/core/prompts.php`.
- Variable catalogs live in `includes/core/variables.php`.

## Generated Image Persistence

- Model generation normalizes base64 output and writes JPGs to WordPress uploads.
- DB rows store `image_url`, `prompt`, user/model attributes, and timestamps.
- Edits and outfits may generate new image URLs and emit frontend events so history cards update.

## When Changing AI Behavior

- Preserve hard constraints in prompts that protect product/model consistency.
- Keep field names aligned across shortcode, JS, AJAX, DB insert, and history rendering.
- If adding a prompt input, update:
  - Markup field.
  - JS validation/sync if required.
  - AJAX sanitizer/read.
  - Prompt data composition.
  - DB schema/insert/response if persisted.
  - History display if user-facing.

## Campaign Prompt Notes

- `product_mode=upload_product` uses uploaded product images as the primary reference.
- `product_mode=model_product` is only valid with a selected model/outfit; the outfit image becomes the primary image reference and the prompt treats the visible clothing/styling as the product being advertised.

## Service Debugging

- First check request field presence and nonce/login.
- Then check prompt text and base64/image source.
- Then inspect Gemini response body shape and image extraction.
- Finally check file write path and returned URL.
