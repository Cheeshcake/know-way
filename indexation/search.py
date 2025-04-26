#!/usr/bin/env python3
import sys
import json
import os
import re
import argparse
from datetime import datetime

class FuzzySearch:
    def __init__(self):
        self.courses_cache = None
        self.cache_file = os.path.join(os.path.dirname(__file__), 'courses_cache.json')
    
    def levenshtein_distance(self, s1, s2):
        """Calculate the Levenshtein distance between two strings."""
        if len(s1) < len(s2):
            return self.levenshtein_distance(s2, s1)
        
        if len(s2) == 0:
            return len(s1)
        
        previous_row = range(len(s2) + 1)
        for i, c1 in enumerate(s1):
            current_row = [i + 1]
            for j, c2 in enumerate(s2):
                # Calculate insertions, deletions and substitutions
                insertions = previous_row[j + 1] + 1
                deletions = current_row[j] + 1
                substitutions = previous_row[j] + (c1 != c2)
                current_row.append(min(insertions, deletions, substitutions))
            previous_row = current_row
        
        return previous_row[-1]
    
    def similarity_score(self, text, query):
        """Calculate similarity score between text and query."""
        # Convert to lowercase for case-insensitive matching
        text = text.lower()
        query = query.lower()
        
        # Exact match gets highest score
        if query in text:
            return 100
        
        # Calculate Levenshtein distance
        distance = self.levenshtein_distance(text, query)
        
        # Convert distance to a similarity score (higher is better)
        max_len = max(len(text), len(query))
        if max_len == 0:
            return 0
        
        similarity = 100 * (1 - distance / max_len)
        return max(0, similarity)  # Ensure non-negative
    
    def search_courses(self, query, courses, threshold=50):
        """Search courses with fuzzy matching."""
        if not query:
            return []
        
        results = []
        for course in courses:
            # Calculate similarity scores for title and description
            title_score = self.similarity_score(str(course.get('title', '')), query)
            desc_score = self.similarity_score(str(course.get('description', '')), query)
            
            # Use the higher of the two scores
            score = max(title_score, desc_score)
            
            # Include courses that meet the threshold
            if score >= threshold:
                course_copy = course.copy()
                course_copy['score'] = score
                results.append(course_copy)
        
        # Sort by similarity score (highest first)
        results.sort(key=lambda x: x['score'], reverse=True)
        return results
    
    def get_courses(self, db_connection):
        """Get courses from database or cache."""
        # Check if we can use cache
        if self.should_use_cache():
            return self.load_from_cache()
        
        # Query database for courses
        cursor = db_connection.cursor(dictionary=True)
        query = """
            SELECT c.*, COUNT(cq.id) as quizzes_count 
            FROM courses c 
            LEFT JOIN course_quizzes cq ON c.id = cq.course_id 
            WHERE c.status = 'published'
            GROUP BY c.id
        """
        cursor.execute(query)
        courses = cursor.fetchall()
        cursor.close()
        
        # Save to cache for future use
        self.save_to_cache(courses)
        
        return courses
    
    def should_use_cache(self):
        """Determine if cache should be used based on freshness."""
        if not os.path.exists(self.cache_file):
            return False
        
        # Check if cache is less than 1 hour old
        cache_mtime = os.path.getmtime(self.cache_file)
        cache_age = datetime.now().timestamp() - cache_mtime
        return cache_age < 3600  # 1 hour in seconds
    
    def load_from_cache(self):
        """Load courses from cache file."""
        try:
            with open(self.cache_file, 'r') as f:
                return json.load(f)
        except (IOError, json.JSONDecodeError):
            return []
    
    def save_to_cache(self, courses):
        """Save courses to cache file."""
        try:
            # Ensure directory exists
            os.makedirs(os.path.dirname(self.cache_file), exist_ok=True)
            
            # Convert MySQL datetime objects to strings if needed
            serializable_courses = []
            for course in courses:
                serializable_course = {}
                for key, value in course.items():
                    if isinstance(value, datetime):
                        serializable_course[key] = value.isoformat()
                    else:
                        serializable_course[key] = value
                serializable_courses.append(serializable_course)
            
            with open(self.cache_file, 'w') as f:
                json.dump(serializable_courses, f)
        except IOError:
            # If cache saving fails, just continue without cache
            pass
    
    def load_courses_from_file(self, file_path):
        """Load courses from a JSON file."""
        try:
            with open(file_path, 'r') as f:
                return json.load(f)
        except (IOError, json.JSONDecodeError) as e:
            raise Exception(f"Could not load courses from file: {str(e)}")

def main():
    """Main entry point for command-line usage."""
    parser = argparse.ArgumentParser(description="Fuzzy search for courses")
    parser.add_argument("query", help="The search query")
    parser.add_argument("--data-file", help="JSON file containing course data")
    parser.add_argument("--threshold", type=int, default=50, help="Similarity threshold (0-100)")
    args = parser.parse_args()
    
    fuzzy_search = FuzzySearch()
    
    try:
        # Load courses from file if provided
        if args.data_file:
            courses = fuzzy_search.load_courses_from_file(args.data_file)
            
            # Perform fuzzy search
            results = fuzzy_search.search_courses(args.query, courses, args.threshold)
            
            # Output results as JSON
            print(json.dumps(results))
            return 0
        else:
            print(json.dumps({"error": "No courses data provided"}))
            return 1
    
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        return 1

if __name__ == "__main__":
    sys.exit(main())
