from rembg import remove
from PIL import Image
import sys

input_path = sys.argv[1]
output_path = sys.argv[2]

image = Image.open(input_path)
result = remove(image)
result.save(output_path)
print("Fondo eliminado correctamente")