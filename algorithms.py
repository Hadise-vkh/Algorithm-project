import json
from Levenshtein import distance as levenshtein_distance
from typing import List, Dict
import numpy as np
import sys
from datetime import datetime
import os

def jaccard_similarity(set1: set, set2: set) -> float:
    intersection = len(set1.intersection(set2))
    union = len(set1.union(set2))
    return intersection / union if union != 0 else 0.0

def analyze_similarity(answer1: str, answer2: str) -> Dict[str, float]:
    levenshtein_dist = levenshtein_distance(answer1, answer2)
    max_len = max(len(answer1), len(answer2))
    levenshtein_score = 1 - (levenshtein_dist / max_len) if max_len != 0 else 0.0

    jaccard_score = jaccard_similarity(set(answer1.split()), set(answer2.split()))

    combined_score = (levenshtein_score + jaccard_score) / 2

    return {
        "levenshtein_score": levenshtein_score,
        "jaccard_score": jaccard_score,
        "combined_score": combined_score,
    }

def detect_time_anomaly(times: List[float], threshold: float = 0.5) -> List[int]:
    if not times:
        return []
    mean_time = np.mean(times)
    anomalies = [i for i, t in enumerate(times) if t < mean_time * threshold]
    return anomalies

def process_exam_data(input_file: str, output_file: str):
    try:
        with open(input_file, "r", encoding="utf-8") as f:
            data = json.load(f)
    except FileNotFoundError:
        print(f"Error: Input file '{input_file}' not found.")
        return
    except json.JSONDecodeError:
        print(f"Error: Invalid JSON format in '{input_file}'.")
        return

    analysis_results = []
    students = list(data.keys())

    # بررسی پاسخ‌های هر دانشجو با سایر دانشجویان
    for i in range(len(students)):
        for j in range(i + 1, len(students)):
            student1_id = students[i]
            student2_id = students[j]
            student1_answers = data[student1_id]
            student2_answers = data[student2_id]
            matching_segments = []

            # مقایسه پاسخ‌های هر سوال بین دو دانشجو
            for q in range(1, 6):  # از سوال 1 تا 5
                answer1 = next((ans["description"] for ans in student1_answers if ans["qnumber"] == q), "")
                answer2 = next((ans["description"] for ans in student2_answers if ans["qnumber"] == q), "")

                similarity_scores = analyze_similarity(str(answer1), str(answer2))
                matching_segments.append({
                    "question_id": f"q{q}",
                    "segment1": {"text": answer1},
                    "segment2": {"text": answer2},
                    "similarity_percentage": similarity_scores["combined_score"] * 100
                })

            if matching_segments:
                overall_similarity = np.mean([seg["similarity_percentage"] for seg in matching_segments])
            else:
                overall_similarity = 0.0

            threshold_settings = {"minimum_similarity": 30, "suspicious_threshold": 70}
            if overall_similarity > threshold_settings["suspicious_threshold"]:
                risk_level = "HIGH"
            elif overall_similarity > threshold_settings["minimum_similarity"]:
                risk_level = "MEDIUM"
            else:
                risk_level = "LOW"

            analysis_results.append({
                "student_pair": {
                    "student1_id": student1_id,
                    "student2_id": student2_id
                },
                "similarity_score": overall_similarity / 100,
                "matching_segments": matching_segments,
                "overall_risk_level": risk_level
            })

    # تحلیل زمان پاسخدهی
    all_times = []
    for student_id in students:
        student_answers = data[student_id]
        student_times = [ans["time_taken"] for ans in student_answers]
        all_times.extend(student_times)
    time_anomalies = detect_time_anomaly(all_times)

    output_data = {
        "quiz_id": "example_quiz",
        "timestamp": datetime.now().isoformat(),
        "analysis_results": analysis_results,
        "time_anomalies": time_anomalies,
        "analysis_metadata": {
            "algorithm_version": "v1.1",
            "threshold_settings": {
                "minimum_similarity": 0.3,
                "suspicious_threshold": 0.7
            }
        }
    }

    # ایجاد پوشه خروجی اگر وجود نداشته باشد
    os.makedirs(os.path.dirname(output_file), exist_ok=True)
    
    with open(output_file, "w", encoding="utf-8") as f:
        json.dump(output_data, f, ensure_ascii=False, indent=4)

if __name__ == "__main__":
    if len(sys.argv) == 3:
        input_file_path = sys.argv[1]
        output_file_path = sys.argv[2]
    else:
        input_file_path = "input.json"
        output_file_path = "output.json"
    process_exam_data(input_file_path, output_file_path)